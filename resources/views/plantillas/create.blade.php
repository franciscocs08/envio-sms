@extends('layouts.app')
@section('title', 'Nueva Plantilla')
@section('page-title', 'Nueva Plantilla SMS')

@section('content')
<div style="max-width:600px">
    <form method="POST" action="{{ route('plantillas.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre de la Plantilla</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre') }}" placeholder="Ej: Recordatorio Pago" maxlength="150" required>
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mensaje (máximo 150 caracteres)</label>
            <textarea name="contenido" id="contenido"
                      class="form-control @error('contenido') is-invalid @enderror"
                      rows="4" maxlength="150" required>{{ old('contenido') }}</textarea>
            <div class="form-text">
                <span id="char-count">0</span>/150 caracteres
            </div>
            @error('contenido')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

        <button type="submit" class="btn btn-primary">Guardar</button>
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
