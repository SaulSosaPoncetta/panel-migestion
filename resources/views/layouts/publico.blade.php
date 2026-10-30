<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('titulo', 'Pagos') — MiGestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f8; }
        .bg-marca { background: #14532d; }
        .btn-marca { background: #14532d; border-color: #14532d; color: #fff; }
        .btn-marca:hover, .btn-marca:focus { background: #1e6b3a; border-color: #1e6b3a; color: #fff; }
        .form-check-input:checked { background-color: #14532d; border-color: #14532d; }
        .plan-opcion { cursor: pointer; }
        .plan-opcion:has(input:checked) { border-color: #14532d !important; box-shadow: 0 0 0 .2rem rgba(20, 83, 45, .15); }
    </style>
</head>
<body>
<div class="container py-4 py-md-5" style="max-width: 560px;">
    <div class="text-center mb-4 text-success-emphasis">
        <i class="bi bi-shield-check fs-2"></i>
        <div class="fw-semibold">MiGestión · Pago seguro</div>
    </div>
    @yield('contenido')
    <p class="text-center text-muted small mt-4 mb-0">Los pagos se procesan con Mercado Pago. No guardamos los datos de tu tarjeta.</p>
</div>
</body>
</html>
