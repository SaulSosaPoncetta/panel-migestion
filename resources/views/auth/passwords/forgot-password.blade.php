<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña — MiGestión Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">

<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="w-100" style="max-width: 440px;">

        <div class="text-center mb-4">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;">
                <i class="bi bi-shop fs-2"></i>
            </div>
            <h3 class="fw-bold">MiGestión Panel</h3>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-3">Recuperar contraseña</h5>
                <p class="text-muted small mb-4">Ingresá tu email y te mandamos un enlace para restablecer tu contraseña.</p>

                @if(session('status'))
                    <div class="alert alert-success py-2 small">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    @foreach($errors->all() as $error)
                        <div class="small"><i class="bi bi-x-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required autofocus>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope me-2"></i>Enviar enlace
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3 small text-muted">
            <a href="{{ route('login') }}" class="text-decoration-none">Volver al login</a>
        </div>

    </div>
</div>
</body>
</html>