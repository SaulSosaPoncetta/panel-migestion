<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar email — MiGestión Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
    .bg-primary { background-color: #14532d !important; }
    .btn-primary {
        background-color: #14532d;
        border-color: #14532d;
    }
    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active {
        background-color: #1e6b3a !important;
        border-color: #1e6b3a !important;
    }
    a { color: #14532d; }
    a:hover { color: #1e6b3a; }
    .form-control:focus, .form-select:focus {
        border-color: #14532d;
        box-shadow: 0 0 0 0.25rem rgba(20, 83, 45, 0.15);
    }
</style>
</head>
<body class="bg-light">

<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="w-100" style="max-width: 480px;">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-2">Verificá tu email</h5>
                <p class="text-muted small mb-4">
                    Gracias por registrarte. Antes de empezar, ¿podrías verificar tu email haciendo clic
                    en el enlace que te acabamos de enviar? Si no te llegó el email, con gusto te mandamos otro.
                </p>

                @if(session('status') == 'verification-link-sent')
                <div class="alert alert-success py-2 small">
                    Te enviamos un nuevo enlace de verificación al email que registraste.
                </div>
                @endif

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope me-2"></i>Reenviar email de verificación
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Salir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>