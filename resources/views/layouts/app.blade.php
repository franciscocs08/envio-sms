<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema SMS') — SMS Masivo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f6f9; }

        #sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1e293b;
            color: #cbd5e1;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        #sidebar .brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #f8fafc;
            border-bottom: 1px solid #334155;
            letter-spacing: .02em;
        }
        #sidebar .nav-link {
            color: #94a3b8;
            padding: .6rem 1.5rem;
            border-radius: 0;
            font-size: .9rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: background .15s, color .15s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: #334155;
            color: #f1f5f9;
        }
        #sidebar .nav-section {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #475569;
            padding: 1rem 1.5rem .3rem;
        }
        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid #334155;
            font-size: .82rem;
            color: #64748b;
        }

        #main-content {
            margin-left: 240px;
            min-height: 100vh;
        }
        #topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .page-content { padding: 1.75rem 1.5rem; }

        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; }

        .badge-recepcionado { background: #e2e8f0; color: #334155; }
        .badge-enviado      { background: #dbeafe; color: #1d4ed8; }
        .badge-entregado    { background: #dcfce7; color: #15803d; }
        .badge-leido        { background: #f0fdf4; color: #166534; border: 1px solid #86efac; }
        .badge-fallido      { background: #fee2e2; color: #b91c1c; }
        .badge-pendiente    { background: #fef9c3; color: #854d0e; }
        .badge-procesando   { background: #dbeafe; color: #1e40af; }
        .badge-completado   { background: #dcfce7; color: #166534; }
        .badge-con-errores  { background: #fee2e2; color: #b91c1c; }
    </style>
    @stack('styles')
</head>
<body>

<nav id="sidebar">
    <div class="brand">
        <!-- <i class="bi bi-chat-dots-fill me-2"></i>--> Plataforma SMS
    </div>
    <div class="mt-2">
        <div class="nav-section">Principal</div>
        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <div class="nav-section">Gestión</div>
        <a href="{{ route('plantillas.index') }}"
           class="nav-link {{ request()->routeIs('plantillas.*') ? 'active' : '' }}">
            <i class="bi bi-file-text"></i> Plantillas SMS
        </a>
        <a href="{{ route('cargas.index') }}"
           class="nav-link {{ request()->routeIs('cargas.*') ? 'active' : '' }}">
            <i class="bi bi-upload"></i> Carga de Datos
        </a>
        <a href="{{ route('envios.index') }}"
           class="nav-link {{ request()->routeIs('envios.*') ? 'active' : '' }}">
            <i class="bi bi-send"></i> Envíos
        </a>
        <div class="nav-section">Análisis</div>
        <a href="{{ route('reportes.index') }}"
           class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Reportes
        </a>
    </div>
    <div class="sidebar-footer">
        <div class="text-truncate fw-medium" style="color:#cbd5e1">{{ auth()->user()->nombre ?? '' }}</div>
        <div class="small" style="color:#64748b">{{ auth()->user()->rol->nombre ?? '' }}</div>
    </div>
</nav>

<div id="main-content">
    <div id="topbar">
        <span class="fw-semibold text-secondary">@yield('page-title', '')</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-box-arrow-right"></i> Salir
            </button>
        </form>
    </div>
    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
