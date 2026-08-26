@extends('layouts.app')

@section('content')
<h3 class="mb-3"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Clientes totales</div>
                <div class="fs-3 fw-semibold">{{ $totalClientes }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Suscripciones activas</div>
                <div class="fs-3 fw-semibold text-success">{{ $suscripcionesPorEstado['activa'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Vencidas / suspendidas</div>
                <div class="fs-3 fw-semibold text-danger">
                    {{ ($suscripcionesPorEstado['vencida'] ?? 0) + ($suscripcionesPorEstado['suspendida'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Cobrado este mes</div>
                <div class="fs-3 fw-semibold">${{ number_format($cobradoEsteMes, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><i class="bi bi-calendar-event me-1"></i>Próximos vencimientos (recurrentes)</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Sistema</th>
                    <th>Plan</th>
                    <th>Próximo cobro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proximosVencimientos as $suscripcion)
                <tr>
                    <td>{{ $suscripcion->clienteSistema->cliente->nombre }}</td>
                    <td>{{ $suscripcion->clienteSistema->sistema->nombre }}</td>
                    <td>{{ $suscripcion->plan }}</td>
                    <td>{{ $suscripcion->proxima_fecha_cobro?->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No hay vencimientos próximos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
