@extends('layouts.app')
@section('title', 'Plantillas SMS')
@section('page-title', 'Plantillas SMS')

@section('content')
<div class="mb-3">
    <a href="{{ route('plantillas.create') }}" class="btn btn-primary btn-sm">Nueva plantilla</a>
</div>

<table class="table table-bordered table-sm bg-white">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Contenido</th>
            <th>Cedente</th>
            <th>Fecha Creación</th>
            <th>Acciones</th>
            <th>Creada Por</th>
        </tr>
    </thead>
    <tbody>
        @forelse($plantillas as $p)
        <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->nombre }}</td>
            <td>{{ $p->contenido }}</td>
            <td>{{ $p->cedente->nombre }}</td>
            <td>{{ $p->created_at->format('d/m/Y') }}</td>

            <td>
                <a href="{{ route('plantillas.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                <form method="POST" action="{{ route('plantillas.destroy', $p) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar plantilla?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
            </td>
            <td>{{ $p->creador->nombre ?? 'N/A' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-muted">No hay plantillas registradas.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $plantillas->links() }}
@endsection
