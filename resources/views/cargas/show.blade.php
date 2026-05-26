@extends('layouts.app')
@section('title', 'Detalle Carga')
@section('page-title', 'Detalle Carga: {{ $carga->nombre }}')

@section('content')
<div class="mb-3">
    <a href="{{ route('cargas.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Volver</a>
</div>

<p>
    <strong>Cedente:</strong> {{ $carga->cedente->nombre }} &nbsp;|&nbsp;
    <strong>Total registros:</strong> {{ number_format($carga->total_registros) }} &nbsp;|&nbsp;
    <strong>Fecha:</strong> {{ $carga->created_at->format('d/m/Y H:i') }}
</p>

<table class="table table-bordered table-sm bg-white">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>RUT</th>
            <th>Teléfono</th>
        </tr>
    </thead>
    <tbody>
        @foreach($contactos as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->rut ?? '—' }}</td>
            <td>{{ $c->telefono }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $contactos->links() }}
@endsection
