@extends('layouts.app')
@section('title', 'Carga de Datos')
@section('page-title', 'Carga de Datos')

@section('content')
<div class="mb-3">
    <a href="{{ route('cargas.create') }}" class="btn btn-primary btn-sm">Nueva carga</a>
</div>

<table class="table table-bordered table-sm bg-white">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Cedente</th>
            <th>Registros</th>
            <th>Fecha</th>
            <th>Acciones</th>
            <th>Creada Por</th>
        </tr>
    </thead>
    <tbody>
        @forelse($cargas as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->nombre }}</td>
            <td>{{ $c->cedente->nombre }}</td>
            <td>{{ number_format($c->total_registros) }}</td>
            <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
            <td>
                <a href="{{ route('cargas.show', $c) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                <form method="POST" action="{{ route('cargas.destroy', $c) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar esta carga? También se eliminarán los contactos.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
            </td>
            <td>{{ $c->creador->nombre ?? 'N/A' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-muted">No hay cargas registradas.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $cargas->links() }}
@endsection
