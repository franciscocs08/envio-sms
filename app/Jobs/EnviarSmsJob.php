<?php

namespace App\Jobs;

use App\Models\Envio;
use App\Models\EnvioDetalle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Cuántos SMS procesa cada job hijo en paralelo
    const CHUNK_SIZE = 100;

    public $tries   = 1;
    public $timeout = 120;

    private $envioId;

    public function __construct(int $envioId)
    {
        $this->envioId = $envioId;
    }

    public function handle()
    {
        $envio = Envio::with('carga.contactos')->findOrFail($this->envioId);

        // Marcar como procesando
        $envio->update(['estado' => 'procesando']);

        $contactos = $envio->carga->contactos;

        if ($contactos->isEmpty()) {
            $envio->update(['estado' => 'con_errores']);
            Log::warning("EnviarSmsJob: envio_id={$this->envioId} no tiene contactos.");
            return;
        }

        // Actualizar total
        $envio->update(['total' => $contactos->count()]);

        // Crear todos los envio_detalles en estado pendiente (en chunks para no saturar memoria)
        $rows = $contactos->map(function ($contacto) {
            return [
                'envio_id'          => $this->envioId,
                'telefono'          => $contacto->telefono,
                'rut'               => $contacto->rut,
                'estado'            => 'pendiente',
                'ops_sms_id'        => null,
                'respuesta_gateway' => null,
                'sent_at'           => null,
            ];
        })->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            EnvioDetalle::insert($chunk);
        }

        // Obtener los IDs recién insertados y despachar un job por cada chunk
        $detalleIds = EnvioDetalle::where('envio_id', $this->envioId)
            ->where('estado', 'pendiente')
            ->pluck('id')
            ->toArray();

        $chunks = array_chunk($detalleIds, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            ProcesarChunkSmsJob::dispatch($this->envioId, $chunk);
        }

        Log::info("EnviarSmsJob: envio_id={$this->envioId} — {$contactos->count()} contactos divididos en " . count($chunks) . " chunks de " . self::CHUNK_SIZE);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("EnviarSmsJob falló — envio_id={$this->envioId}: " . $exception->getMessage());
        Envio::where('id', $this->envioId)->update(['estado' => 'con_errores']);
    }
}
