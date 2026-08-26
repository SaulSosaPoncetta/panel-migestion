<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — MiGestión Panel</title>
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
    @if($captchaActivo)
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</head>
<body class="bg-light">

<div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="w-100" style="max-width: 440px;">

        <div class="text-center mb-4">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;">
                <i class="bi bi-diagram-3-fill fs-2"></i>
            </div>
            <h3 class="fw-bold">MiGestión Panel</h3>
            <p class="text-muted">Clientes y cobros de tus sistemas SaaS</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <h5 class="fw-semibold mb-4">Iniciar sesión</h5>

                @if($intentos > 0 && $intentos < $umbral)
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Intentos fallidos: <strong>{{ $intentos }}</strong> de {{ $umbral }}.
                    Después del intento {{ $umbral }} se requerirá verificación de seguridad.
                </div>
                @endif

                @if($captchaActivo)
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="bi bi-shield-lock me-1"></i>
                    Verificación de seguridad requerida por múltiples intentos fallidos.
                </div>
                @endif

                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    @foreach($errors->all() as $error)
                        <div class="small"><i class="bi bi-x-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required autofocus autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePass()" title="Ver contraseña">
                                <i class="bi bi-eye" id="ico-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>

                    @if($captchaActivo)
                    <div class="mb-3">
                        <div class="cf-turnstile" data-sitekey="{{ $siteKey }}"
                             data-theme="light" data-language="es"></div>
                        @error('captcha')
                        <div class="text-danger small mt-1"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>
                    </div>

                </form>

                <div class="mt-3 text-center small text-muted">
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none">
                        ¿Olvidaste tu contraseña?
                    </a>
                    @endif
                </div>

                @if($intentos > 0)
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><i class="bi bi-shield me-1"></i>Seguridad</span>
                        <span>{{ $intentos }} / 6 intentos</span>
                    </div>
                    @php
                        $pct   = min(100, intval($intentos / 6 * 100));
                        $color = $intentos <= 2 ? 'success' : ($intentos <= 4 ? 'warning' : 'danger');
                    @endphp
                    <div class="progress" style="height:4px">
                        <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                    @if($intentos >= 6)
                    <div class="text-danger small mt-1 text-center">
                        <i class="bi bi-lock me-1"></i>Cuenta bloqueada temporalmente.
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

<script>
function togglePass() {
    const p   = document.getElementById('password');
    const ico = document.getElementById('ico-eye');
    if (p.type === 'password') {
        p.type = 'text';
        ico.className = 'bi bi-eye-slash';
    } else {
        p.type = 'password';
        ico.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>