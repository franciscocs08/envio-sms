@extends('layouts.app')
@section('title', 'Envíos')
@section('page-title', 'Envíos')

@section('content')
<div class="mb-3">
    <a href="{{ route('envios.create') }}" class="btn btn-primary btn-sm">Nuevo envío</a>
</div>

<table class="table table-bordered table-sm bg-white">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Cedente</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Enviados</th>
            <th>Fallidos</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($envios as $e)
        <tr>
            <td>{{ $e->id }}</td>
            <td>{{ $e->nombre }}</td>
            <td>{{ $e->cedente->nombre }}</td>
            <td><span class="badge badge-{{ str_replace('_', '-', $e->estado) }}">{{ $e->estado }}</span></td>
            <td>{{ number_format($e->total) }}</td>
            <td>{{ number_format($e->enviados) }}</td>
            <td>{{ number_format($e->fallidos) }}</td>
            <td>{{ $e->created_at->format('d/m/Y H:i') }}</td>
            <td>
                <a href="{{ route('envios.show', $e) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                @if($e->estado !== 'procesando')
                <form method="POST" action="{{ route('envios.destroy', $e) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este envío?, Ya se ha enviado los SMS solo borrará el registro')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-muted">No hay envíos registrados.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $envios->links() }}
@endsection
