@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .kpi-card { border-left: 4px solid transparent; }
    .kpi-card.total     { border-color: #6366f1; }
    .kpi-card.entregado { border-color: #22c55e; }
    .kpi-card.leido     { border-color: #14b8a6; }
    .kpi-card.fallido   { border-color: #ef4444; }
    .kpi-value { font-size: 2rem; font-weight: 700; }
</style>
@endpush

@section('content')

{{-- Filtro fechas --}}
<form method="GET" action="{{ route('dashboard') }}" class="row g-2 mb-4 align-items-end">
    <div class="col-auto">
        <label class="form-label mb-1 small fw-medium">Desde</label>
        <input type="date" name="fecha_inicio" class="form-control form-control-sm"
               value="{{ request('fecha_inicio') }}">
    </div>
    <div class="col-auto">
        <label class="form-label mb-1 small fw-medium">Hasta</label>
        <input type="date" name="fecha_fin" class="form-control form-control-sm"
               value="{{ request('fecha_fin') }}">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm ms-1">Limpiar</a>
    </div>
</form>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card total p-3">
            <div class="text-muted small mb-1">Total enviados</div>
            <div class="kpi-value text-indigo">{{ number_format($kpis['total']) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card entregado p-3">
            <div class="text-muted small mb-1">Entregados</div>
            <div class="kpi-value" style="color:#22c55e">{{ number_format($kpis['entregados']) }}</div>
        </div>
    </div>
    <!-- <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card leido p-3">
            <div class="text-muted small mb-1">Leídos</div>
            <div class="kpi-value" style="color:#14b8a6">{{ number_format($kpis['leidos']) }}</div>
        </div>
    </div> -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card fallido p-3">
            <div class="text-muted small mb-1">Fallidos</div>
            <div class="kpi-value" style="color:#ef4444">{{ number_format($kpis['fallidos']) }}</div>
        </div>
    </div>
</div>

<!-- {{-- Gráfico --}}
<div class="card">
    <div class="card-header py-3">Envíos por cedente</div>
    <div class="card-body">
        @if($enviosPorCedente->isEmpty())
            <p class="text-muted mb-0">Sin datos para mostrar.</p>
        @else
            <canvas id="graficoEnvios" height="100"></canvas>
        @endif
    </div>
</div> -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
@if(!$enviosPorCedente->isEmpty())
const ctx = document.getElementById('graficoEnvios');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($enviosPorCedente->keys()) !!},
        datasets: [{
            label: 'SMS enviados',
            data: {!! json_encode($enviosPorCedente->values()) !!},
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
@endif
</script>
@endpush
