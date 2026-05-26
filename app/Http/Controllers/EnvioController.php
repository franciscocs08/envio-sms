<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarSmsJob;
use App\Models\Carga;
use App\Models\Cedente;
use App\Models\Envio;
use App\Models\Plantilla;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $envios = Envio::with('cedente')
            ->when(!$user->esAdmin(), fn($q) => $q->where('cedente_id', $user->cedente_id))
            ->latest()
            ->paginate(20);

        return view('envios.index', compact('envios'));
    }

    public function create()
    {
        $user     = auth()->user();
        $cedentes = $user->esAdmin()
            ? Cedente::orderBy('nombre')->get()
            : Cedente::where('id', $user->cedente_id)->get();

        $cargas = Carga::with('cedente')
            ->when(!$user->esAdmin(), fn($q) => $q->where('cedente_id', $user->cedente_id))
            ->where('total_registros', '>', 0)
            ->orderBy('nombre')
            ->get();

        $plantillas = Plantilla::with('cedente')
            ->when(!$user->esAdmin(), fn($q) => $q->where('cedente_id', $user->cedente_id))
            ->orderBy('nombre')
            ->get();

        return view('envios.create', compact('cedentes', 'cargas', 'plantillas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:150',
            'cedente_id'  => 'required|exists:cedentes,id',
            'carga_id'    => 'required|exists:cargas,id',
            'plantilla_id' => 'required|exists:plantillas_sms,id',
        ]);

        $user = auth()->user();

        // Verificar que la carga y plantilla pertenecen al cedente seleccionado
        $carga = Carga::findOrFail($data['carga_id']);
        if ($carga->cedente_id != $data['cedente_id']) {
            return back()->withInput()->with('error', 'La carga seleccionada no pertenece al cedente.');
        }

        $plantilla = Plantilla::findOrFail($data['plantilla_id']);
        if ($plantilla->cedente_id != $data['cedente_id']) {
            return back()->withInput()->with('error', 'La plantilla seleccionada no pertenece al cedente.');
        }

        if (!$user->esAdmin() && $data['cedente_id'] != $user->cedente_id) {
            abort(403);
        }

        // Verificar que el cedente tiene token configurado
        $cedente = Cedente::findOrFail($data['cedente_id']);
        if (empty($cedente->ops_token)) {
            return back()->withInput()->with('error', 'El cedente no tiene configurado el token OPS. Contacte al administrador.');
        }

        // ops_from: usa el del cedente, si no tiene usa el default del .env
        $opsFrom = $cedente->ops_from ?: config('app.ops_from_default');
        if (empty($opsFrom)) {
            return back()->withInput()->with('error', 'No hay número origen OPS configurado. Contacte al administrador.');
        }

        $envio = Envio::create([
            'nombre'       => $data['nombre'],
            'cedente_id'   => $data['cedente_id'],
            'carga_id'     => $data['carga_id'],
            'plantilla_id' => $data['plantilla_id'],
            'estado'       => 'pendiente',
            'total'        => $carga->total_registros,
            'enviados'     => 0,
            'fallidos'     => 0,
            'created_by'   => $user->id,
        ]);

        EnviarSmsJob::dispatch($envio->id);

        return redirect()->route('envios.show', $envio)->with('success', 'Envío iniciado. Procesando en segundo plano...');
    }

    public function show($id)
    {
        $envio = $this->findAuthorized($id);
        $envio->load('plantilla', 'carga', 'cedente');
        return view('envios.show', compact('envio'));
    }

    public function destroy($id)
    {
        $envio = $this->findAuthorized($id);

        if ($envio->estado === 'procesando') {
            return back()->with('error', 'No se puede eliminar un envío en proceso.');
        }
        
        $envio->delete();
        return redirect()->route('envios.index')->with('success', 'Envío eliminado.');
    }

    /**
     * Endpoint AJAX para polling de progreso.
     * Retorna JSON con estado y contadores.
     */
    public function progreso($id)
    {
        $envio = $this->findAuthorized($id);

        return response()->json([
            'estado'    => $envio->estado,
            'total'     => $envio->total,
            'enviados'  => $envio->enviados,
            'fallidos'  => $envio->fallidos,
            'porcentaje' => $envio->total > 0
                ? round(($envio->enviados + $envio->fallidos) / $envio->total * 100)
                : 0,
        ]);
    }

    private function findAuthorized(int $id): Envio
    {
        $user  = auth()->user();
        $envio = Envio::findOrFail($id);

        if (!$user->esAdmin() && $envio->cedente_id !== $user->cedente_id) {
            abort(403);
        }

        return $envio;
    }
}
