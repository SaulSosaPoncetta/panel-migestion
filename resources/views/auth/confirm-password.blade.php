<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar contraseña — MiGestión Panel</title>
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
    <div class="w-100" style="max-width: 440px;">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-2">Confirmá tu contraseña</h5>
                <p class="text-muted small mb-4">Esta es un área protegida. Confirmá tu contraseña antes de continuar.</p>

                @if($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    @foreach($errors->all() as $error)
                        <div class="small"><i class="bi bi-x-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contraseña</label>
                        <input type="password" name="password" class="form-control" required autofocus>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>