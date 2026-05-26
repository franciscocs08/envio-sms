@extends('layouts.app')
@section('title', 'Editar Plantilla')
@section('page-title', 'Editar Plantilla SMS')

@section('content')
<div style="max-width:600px">
    <form method="POST" action="{{ route('plantillas.update', $plantilla) }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre de la Plantilla</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre', $plantilla->nombre) }}" maxlength="150" required>
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mensaje (máximo 150 caracteres)</label>
            <textarea name="contenido" id="contenido"
                      class="form-control @error('contenido') is-invalid @enderror"
                      rows="4" maxlength="150" required>{{ old('contenido', $plantilla->contenido) }}</textarea>
            <div class="form-text">
                <span id="char-count">0</span>/150 caracteres
            </div>
            @error('contenido')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Cedente</label>
            <input type="text" class="form-control" value="{{ $plantilla->cedente->nombre }}" disabled>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('plantillas.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const textarea  = document.getElementById('contenido');
    const charCount = document.getElementById('char-count');

    function update() {
        const len = textarea.value.length;
        charCount.textContent = len;
        charCount.style.color = len >= 150 ? 'red' : '';
    }

    textarea.addEventListener('input', update);
    update();
</script>
@endpush
