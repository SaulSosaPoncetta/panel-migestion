<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MiGestión Panel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body {
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #3b0764;
            transition: width .2s ease;
            flex-shrink: 0;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: 68px;
        }

        .sidebar .brand {
            color: #ffffff;
        }

        .sidebar .brand-text {
            white-space: nowrap;
        }

        .sidebar.collapsed .brand-text,
        .sidebar.collapsed .nav-header,
        .sidebar.collapsed .link-label {
            display: none;
        }

        .sidebar .btn-toggle {
            color: #ffffff;
            background: transparent;
            border: none;
        }

        .sidebar .btn-toggle:hover {
            background: rgba(255, 255, 255, .1);
        }

        .sidebar .nav-link {
            color: #ffffff;
            padding: .55rem .85rem;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: .55rem 0;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff;
            background: #5b21b6;
            border-radius: .375rem;
        }

        .sidebar .nav-header {
            color: #bfe6cd;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .75rem 1rem .25rem;
        }

        .main-content {
            flex: 1;
            min-height: 100vh;
            background: #f4f6f8;
            min-width: 0;
        }
    </style>
</head>

<body>
    <div id="app" class="d-flex">
        <nav class="sidebar d-flex flex-column p-2" id="sidebar">
            <div class="d-flex align-items-center justify-content-between px-1 py-2 mb-2">
                <a class="brand d-flex align-items-center text-decoration-none" href="{{ route('dashboard') }}">
                    <i class="bi bi-diagram-3-fill fs-4"></i>
                    <span class="brand-text fs-5 fw-semibold ms-2">MiGestión Panel</span>
                </a>
                <button type="button" class="btn-toggle" id="btn-toggle-sidebar" title="Colapsar/expandir menú">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="nav-header">Panel</div>
            <a class="nav-link {{ request()->is('home') ? 'active' : '' }}" href="{{ route('dashboard') }}"
                title="Dashboard">
                <i class="bi bi-speedometer2"></i><span class="link-label ms-2">Dashboard</span>
            </a>

            <div class="nav-header">Clientes y cobros</div>
            <a class="nav-link {{ request()->is('clientes*') ? 'active' : '' }}" href="{{ route('clientes.index') }}"
                title="Clientes">
                <i class="bi bi-people"></i><span class="link-label ms-2">Clientes</span>
            </a>

            <div class="nav-header">SaaS</div>
            <a class="nav-link {{ request()->is('sistemas*') ? 'active' : '' }}" href="{{ route('sistemas.index') }}"
                title="Sistemas SaaS">
                <i class="bi bi-hdd-network"></i><span class="link-label ms-2">Sistemas SaaS</span>
            </a>

            <div class="mt-auto">
                <a class="nav-link" href="{{ route('logout') }}" title="Salir"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-left"></i><span class="link-label ms-2">Salir
                        ({{ Auth::user()->name ?? '' }})</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </nav>

        <div class="main-content">
            <div class="container-fluid py-4 px-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const btn = document.getElementById('btn-toggle-sidebar');
            const guardado = localStorage.getItem('sidebarCollapsed') === '1';
            if (guardado) sidebar.classList.add('collapsed');

            btn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
            });
        })();
    </script>
</body>

</html>
