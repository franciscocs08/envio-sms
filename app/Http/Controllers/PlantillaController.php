<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\Cedente;
use Illuminate\Http\Request;

class PlantillaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $plantillas = Plantilla::with('cedente')
            ->when(!$user->esAdmin(), fn($q) => $q->where('cedente_id', $user->cedente_id))
            ->latest()
            ->paginate(20);

        return view('plantillas.index', compact('plantillas'));
    }

    public function create()
    {
        $cedentes = $this->cedentesDisponibles();
        return view('plantillas.create', compact('cedentes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:155',
            'contenido'  => 'required|string|max:155',
            'cedente_id' => 'required|exists:cedentes,id',
        ]);

        $user = auth()->user();

        if (!$user->esAdmin() && $data['cedente_id'] != $user->cedente_id) {
            abort(403);
        }

        Plantilla::create([
            'nombre'     => $data['nombre'],
            'contenido'  => $data['contenido'],
            'cedente_id' => $data['cedente_id'],
            'created_by' => $user->id,
        ]);

        return redirect()->route('plantillas.index')->with('success', 'Plantilla creada correctamente.');
    }

    public function show($id)
    {
        $plantilla = $this->findAuthorized($id);
        return view('plantillas.show', compact('plantilla'));
    }

    public function edit($id)
    {
        $plantilla = $this->findAuthorized($id);
        $cedentes  = $this->cedentesDisponibles();
        return view('plantillas.edit', compact('plantilla', 'cedentes'));
    }

    public function update(Request $request, $id)
    {
        $plantilla = $this->findAuthorized($id);

        $data = $request->validate([
            'nombre'    => 'required|string|max:155',
            'contenido' => 'required|string|max:155',
        ]);

        $plantilla->update($data);

        return redirect()->route('plantillas.index')->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy($id)
    {
        $plantilla = $this->findAuthorized($id);
        $plantilla->delete();

        return redirect()->route('plantillas.index')->with('success', 'Plantilla eliminada.');
    }

    private function findAuthorized(int $id): Plantilla
    {
        $user      = auth()->user();
        $plantilla = Plantilla::findOrFail($id);

        if (!$user->esAdmin() && $plantilla->cedente_id !== $user->cedente_id) {
            abort(403);
        }

        return $plantilla;
    }

    private function cedentesDisponibles()
    {
        $user = auth()->user();
        return $user->esAdmin()
            ? Cedente::orderBy('nombre')->get()
            : Cedente::where('id', $user->cedente_id)->get();
    }
}
