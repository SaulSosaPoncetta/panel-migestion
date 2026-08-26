<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña — MiGestión Panel</title>
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

        <div class="text-center mb-4">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;">
                <i class="bi bi-shop fs-2"></i>
            </div>
            <h3 class="fw-bold">MiGestión Panel</h3>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-semibold mb-4">Restablecer contraseña</h5>

                @if($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    @foreach($errors->all() as $error)
                        <div class="small"><i class="bi bi-x-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nueva contraseña</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key me-2"></i>Restablecer contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>