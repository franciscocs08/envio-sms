@extends('layouts.app')
@section('title', 'Nueva Carga')
@section('page-title', 'Nueva Carga de Datos')

@section('content')
<div style="max-width:600px">
    <div class="alert alert-info alert-sm">
        <strong>Formato esperado del CSV:</strong><br>
        Columna A: RUT &nbsp;|&nbsp; Columna B: Teléfono (con o sin prefijo 56)<br>
        Ejemplo: <code>12345678-9;56987654321</code> o <code>12345678-9,987654321</code>
    </div>

    <form method="POST" action="{{ route('cargas.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre de la carga</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre') }}" maxlength="150" required>
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Cedente</label>
            <select name="cedente_id" class="form-select @error('cedente_id') is-invalid @enderror" required>
                <option value="">Seleccionar...</option>
                @foreach($cedentes as $c)
                    <option value="{{ $c->id }}" {{ old('cedente_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->nombre }}
                    </option>
                @endforeach
            </select>
            @error('cedente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Archivo CSV</label>
            <input type="file" name="archivo" class="form-control @error('archivo') is-invalid @enderror"
                   accept=".csv,.txt" required>
            @error('archivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Subir y procesar</button>
        <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </form>
</div>
@endsection
