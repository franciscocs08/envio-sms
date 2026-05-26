<?php

namespace App\Jobs;

use App\Models\Envio;
use App\Models\EnvioDetalle;
use App\Services\OpsSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcesarChunkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 1;
    public $timeout = 300;

    private $envioId;
    private $detalleIds;

    public function __construct(int $envioId, array $detalleIds)
    {
        $this->envioId    = $envioId;
        $this->detalleIds = $detalleIds;
    }

    public function handle(OpsSmsService $ops)
    {
        $envio = Envio::with('cedente', 'plantilla')->findOrFail($this->envioId);

        $token   = $envio->cedente->ops_token;
        $from    = $envio->cedente->ops_from ?: config('app.ops_from_default');
        $mensaje = $envio->plantilla->contenido;

        $detalles = EnvioDetalle::whereIn('id', $this->detalleIds)
            ->where('estado', 'pendiente')
            ->get();

        foreach ($detalles as $detalle) {
            $resultado = $ops->send($detalle->telefono, $mensaje, $token, $from);

            if ($resultado['success']) {
                $detalle->update([
                    'estado'     => 'Enviado',
                    'ops_sms_id' => $resultado['ops_sms_id'],
                    'sent_at'    => now(),
                ]);
                $envio->increment('enviados');
            } else {
                $detalle->update([
                    'estado'            => 'Fallido',
                    'respuesta_gateway' => $resultado['error'],
                    'sent_at'           => now(),
                ]);
                $envio->increment('fallidos');
            }
        }

        // Verificar si todos los chunks terminaron para marcar el envío como completado
        $this->verificarCompletado($envio);
    }

    private function verificarCompletado(Envio $envio)
    {
        $envio->refresh();

        $pendientes = EnvioDetalle::where('envio_id', $envio->id)
            ->where('estado', 'pendiente')
            ->count();

        if ($pendientes === 0) {
            $estadoFinal = $envio->fallidos > 0 ? 'con_errores' : 'completado';
            $envio->update(['estado' => $estadoFinal]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("ProcesarChunkSmsJob falló — envio_id={$this->envioId}: " . $exception->getMessage());

        // Marcar los detalles de este chunk como fallidos
        EnvioDetalle::whereIn('id', $this->detalleIds)
            ->where('estado', 'pendiente')
            ->update([
                'estado'            => 'Fallido',
                'respuesta_gateway' => 'Error interno del job: ' . $exception->getMessage(),
                'sent_at'           => now(),
            ]);

        // Recalcular contadores y estado del envío
        $envio = Envio::find($this->envioId);
        if ($envio) {
            $fallidos = EnvioDetalle::where('envio_id', $envio->id)
                ->where('estado', 'Fallido')->count();
            $enviados = EnvioDetalle::where('envio_id', $envio->id)
                ->where('estado', 'Enviado')->count();
            $envio->update([
                'enviados' => $enviados,
                'fallidos' => $fallidos,
                'estado'   => 'con_errores',
            ]);
        }
    }
}
