<?php

namespace App\Http\Controllers;

use App\Models\EnvioDetalle;
use App\Models\Envio;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = EnvioDetalle::query()
            ->join('envios', 'envio_detalles.envio_id', '=', 'envios.id');

        if (!$user->esAdmin()) {
            $query->where('envios.cedente_id', $user->cedente_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('envio_detalles.sent_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('envio_detalles.sent_at', '<=', $request->fecha_fin);
        }

        $kpis = [
            'total'      => (clone $query)->count(),
            'entregados' => (clone $query)->where('envio_detalles.estado', 'Entregado')->count(),
            'leidos'     => (clone $query)->where('envio_detalles.estado', 'Leído')->count(),
            'expirados'   => (clone $query)->where('envio_detalles.estado', 'Expirado')->count(),
            'fallidos'   => (clone $query)->where('envio_detalles.estado', 'Fallido')->count(),
        ];

        $enviosPorCedente = Envio::query()
            ->join('cedentes', 'envios.cedente_id', '=', 'cedentes.id')
            ->when(!$user->esAdmin(), function ($q) use ($user) {
                $q->where('envios.cedente_id', $user->cedente_id);
            })
            ->selectRaw('cedentes.nombre as cedente, SUM(envios.enviados) as total')
            ->groupBy('cedentes.nombre')
            ->pluck('total', 'cedente');

        return view('dashboard', compact('kpis', 'enviosPorCedente'));
    }
}
