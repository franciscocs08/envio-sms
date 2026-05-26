<?php

namespace App\Console\Commands;

use App\Models\Cedente;
use App\Models\EnvioDetalle;
use App\Services\OpsSmsService;
use Illuminate\Console\Command;

class SyncEstadosSms extends Command
{
    protected $signature = 'sms:sync-estados
                            {--cedente_id= : Sincronizar solo un cedente específico}
                            {--limite=500  : Máximo de registros a procesar por ejecución}';

    protected $description = 'Sincroniza los estados de SMS enviados consultando la API de OPS';

    public function handle(OpsSmsService $ops)
    {
        $cedenteId = $this->option('cedente_id');
        $limite    = (int) $this->option('limite');

        // Estados que aún pueden cambiar (no son finales)
        $estadosPendientes = ['Enviado', 'Recepcionado'];

        // Obtener detalles con ops_sms_id y estado no final
        $query = EnvioDetalle::whereIn('estado', $estadosPendientes)
            ->whereNotNull('ops_sms_id')
            ->with('envio.cedente')
            ->limit($limite);

        if ($cedenteId) {
            $query->whereHas('envio', fn($q) => $q->where('cedente_id', $cedenteId));
        }

        $detalles = $query->get();

        if ($detalles->isEmpty()) {
            $this->info('No hay SMS pendientes de sincronizar.');
            return 0;
        }

        $this->info("Sincronizando {$detalles->count()} SMS...");

        // Agrupar por cedente para hacer una sola llamada por cedente
        $porCedente = $detalles->groupBy(fn($d) => $d->envio->cedente_id);

        $actualizados = 0;
        $errores      = 0;

        foreach ($porCedente as $cId => $items) {
            $cedente = $items->first()->envio->cedente;
            $token   = $cedente->ops_token;

            if (empty($token)) {
                $this->warn("Cedente '{$cedente->nombre}' sin token. Saltando.");
                continue;
            }

            // OPS acepta múltiples IDs en un solo request — los enviamos en chunks de 100
            $chunks = $items->chunk(100);

            foreach ($chunks as $chunk) {
                $ids       = $chunk->pluck('ops_sms_id')->toArray();
                $resultado = $ops->buscarPorIds($ids, $token);

                if (!$resultado['success']) {
                    $this->warn("Error consultando cedente '{$cedente->nombre}': {$resultado['error']}");
                    $errores++;
                    continue;
                }

                // Indexar respuesta por smsId para lookup rápido
                $porId = collect($resultado['data'])->keyBy('smsId');

                foreach ($chunk as $detalle) {
                    $info = $porId->get($detalle->ops_sms_id);

                    if (!$info) {
                        continue;
                    }

                    $estadoNuevo = $info['estado'] ?? null;

                    if ($estadoNuevo && $estadoNuevo !== $detalle->estado) {
                        $detalle->update(['estado' => $estadoNuevo]);
                        $actualizados++;
                        $this->line("  SMS {$detalle->ops_sms_id}: {$detalle->estado} → {$estadoNuevo}");
                    }
                }
            }
        }

        $this->info("Sincronización completa. Actualizados: {$actualizados} | Errores: {$errores}");
        return 0;
    }
}
