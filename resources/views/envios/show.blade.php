@extends('layouts.app')
@section('title', 'Detalle Envío')
@section('page-title', 'Envío: {{ $envio->nombre }}')

@section('content')
<div class="mb-3">
    <a href="{{ route('envios.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Volver</a>
</div>

<table class="table table-sm table-bordered bg-white" style="max-width:500px">
    <tr><th>Cedente</th><td>{{ $envio->cedente->nombre }}</td></tr>
    <tr><th>Plantilla</th><td>{{ $envio->plantilla->nombre }}</td></tr>
    <tr><th>Carga</th><td>{{ $envio->carga->nombre }}</td></tr>
    <tr><th>Estado</th>
        <td><span id="badge-estado" class="badge badge-{{ str_replace('_','-',$envio->estado) }}">{{ $envio->estado }}</span></td>
    </tr>
    <tr><th>Total</th><td id="val-total">{{ $envio->total }}</td></tr>
    <tr><th>Enviados</th><td id="val-enviados">{{ $envio->enviados }}</td></tr>
    <tr><th>Fallidos</th><td id="val-fallidos">{{ $envio->fallidos }}</td></tr>
    <tr><th>Fecha</th><td>{{ $envio->created_at->format('d/m/Y H:i') }}</td></tr>
</table>

@if(in_array($envio->estado, ['pendiente', 'procesando']))
<div class="mt-3" style="max-width:500px">
    <div class="progress">
        <div id="barra-progreso" class="progress-bar progress-bar-striped progress-bar-animated"
             role="progressbar" style="width:0%">0%</div>
    </div>
    <p class="text-muted small mt-1" id="txt-progreso">Esperando inicio del proceso...</p>
</div>
@endif
@endsection

@push('scripts')
@if(in_array($envio->estado, ['pendiente', 'procesando']))
<script>
    const progresoUrl = "{{ route('envios.progreso', $envio) }}";
    const estadosFinales = ['completado', 'con_errores'];

    function actualizarProgreso() {
        fetch(progresoUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('val-enviados').textContent  = data.enviados;
            document.getElementById('val-fallidos').textContent  = data.fallidos;
            document.getElementById('val-total').textContent     = data.total;

            const barra = document.getElementById('barra-progreso');
            barra.style.width     = data.porcentaje + '%';
            barra.textContent     = data.porcentaje + '%';

            document.getElementById('badge-estado').textContent  = data.estado;
            document.getElementById('badge-estado').className    = 'badge badge-' + data.estado.replace('_', '-');

            document.getElementById('txt-progreso').textContent =
                data.enviados + ' enviados / ' + data.fallidos + ' fallidos de ' + data.total;

            if (!estadosFinales.includes(data.estado)) {
                setTimeout(actualizarProgreso, 3000);
            } else {
                const barra = document.getElementById('barra-progreso');
                barra.classList.remove('progress-bar-animated');
            }
        })
        .catch(() => setTimeout(actualizarProgreso, 5000));
    }

    setTimeout(actualizarProgreso, 2000);
</script>
@endif
@endpush
