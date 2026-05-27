@extends('layouts.app')
@section('title', 'Nuevo Envío')
@section('page-title', 'Nuevo Envío SMS')

@section('content')
<div style="max-width:600px">
    <form method="POST" action="{{ route('envios.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre identificador del envío</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                   value="{{ old('nombre') }}" maxlength="150" required>
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Cedente</label>
            <select name="cedente_id" id="cedente_id"
                    class="form-select @error('cedente_id') is-invalid @enderror" required>
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
            <label class="form-label">Carga de contactos</label>
            <select name="carga_id" class="form-select @error('carga_id') is-invalid @enderror" required>
                <option value="">Seleccionar...</option>
                @foreach($cargas as $c)
                    <option value="{{ $c->id }}"
                            data-cedente="{{ $c->cedente_id }}"
                            {{ old('carga_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->nombre }} ({{ number_format($c->total_registros) }} contactos — {{ $c->cedente->nombre }})
                    </option>
                @endforeach
            </select>
            @error('carga_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Plantilla SMS</label>
            <select name="plantilla_id" class="form-select @error('plantilla_id') is-invalid @enderror" required>
                <option value="">Seleccionar...</option>
                @foreach($plantillas as $p)
                    <option value="{{ $p->id }}"
                            data-cedente="{{ $p->cedente_id }}"
                            data-contenido="{{ $p->contenido }}"
                            {{ old('plantilla_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nombre }} — {{ $p->cedente->nombre }}
                    </option>
                @endforeach
            </select>
            @error('plantilla_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3" id="preview-contenido" style="display:none">
            <label class="form-label">Vista previa del mensaje</label>
            <div class="form-control bg-light" id="texto-preview" style="min-height:60px;white-space:pre-wrap"></div>
        </div>

        <button type="submit" class="btn btn-primary"
                onclick="return confirm('¿Confirmar envío masivo de SMS?')">
            Iniciar envío
        </button>
        <a href="{{ route('envios.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cedenteSelect = document.getElementById('cedente_id');
    const cargaSelect = document.querySelector('[name=carga_id]');
    const plantillaSelect = document.querySelector('[name=plantilla_id]');
    const preview = document.getElementById('preview-contenido');
    const texto = document.getElementById('texto-preview');

    // Función genérica para validar la relación con el Cedente
    function validarCedente(selectElement, tipoElemento) {
        const opt = selectElement.options[selectElement.selectedIndex];
        const cedenteId = cedenteSelect.value;

        // Si hay un elemento seleccionado y un cedente seleccionado
        if (opt.value && cedenteId) {
            const elementoCedente = opt.dataset.cedente;

            // Si el cedente del elemento no coincide con el seleccionado en el formulario
            if (elementoCedente && elementoCedente !== cedenteId) {
                alert(`La ${tipoElemento} seleccionada pertenece a un cedente diferente al seleccionado. Por favor, elija una opción compatible o cambie el cedente.`);
                
                // Resetear el select que causó el conflicto
                selectElement.value = '';
                
                // Si lo que falló fue la plantilla, ocultamos la vista previa
                if (tipoElemento === 'plantilla') {
                    texto.textContent = '';
                    preview.style.display = 'none';
                }
                return false;
            }
        }
        return true;
    }

    // Evento para el cambio de Carga
    cargaSelect.addEventListener('change', function () {
        validarCedente(this, 'carga de contactos');
    });

    // Evento para el cambio de Plantilla
    plantillaSelect.addEventListener('change', function () {
        if (validarCedente(this, 'plantilla')) {
            // Si la validación pasa, mostrar la vista previa
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                texto.textContent = opt.dataset.contenido;
                preview.style.display = '';
            } else {
                preview.style.display = 'none';
            }
        }
    });

    // Evento por si cambian el Cedente cuando ya hay cosas seleccionadas
    cedenteSelect.addEventListener('change', function () {
        // Validar ambos selectores con el nuevo cedente
        if (cargaSelect.value) {
            validarCedente(cargaSelect, 'carga de contactos');
        }
        if (plantillaSelect.value) {
            validarCedente(plantillaSelect, 'plantilla');
        }
    });
});
</script>
@endpush