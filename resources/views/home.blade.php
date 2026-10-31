@extends('layouts.app')

@section('content')
<h3 class="mb-3"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h3>

@if($transferenciasPorRevisar > 0)
<div class="alert alert-warning d-flex justify-content-between align-items-center">
    <span><i class="bi bi-hourglass-split me-1"></i>
        Tenés <strong>{{ $transferenciasPorRevisar }}</strong> {{ $transferenciasPorRevisar === 1 ? 'transferencia' : 'transferencias' }} con comprobante esperando tu revisión.</span>
    <a href="{{ route('pagos.index', ['estado' => 'pendiente', 'medio' => 'transferencia']) }}" class="btn btn-sm btn-warning">Revisar</a>
</div>
@endif

{{-- Totales de todos los sistemas --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Clientes totales</div>
                <div class="fs-3 fw-semibold">{{ $totalClientes }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Suscripciones activas</div>
                <div class="fs-3 fw-semibold text-success">{{ $suscripcionesPorEstado['activa'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Vencidas / suspendidas</div>
                <div class="fs-3 fw-semibold text-danger">
                    {{ ($suscripcionesPorEstado['vencida'] ?? 0) + ($suscripcionesPorEstado['suspendida'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Ingreso mensual recurrente</div>
                <div class="fs-3 fw-semibold">${{ number_format($ingresoRecurrente, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Cobrado este mes</div>
                <div class="fs-3 fw-semibold">${{ number_format($cobradoEsteMes, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Una tarjeta por sistema --}}
<h5 class="mb-3"><i class="bi bi-grid me-2"></i>Mis sistemas</h5>
<div class="row g-3 mb-4">
    @forelse($tarjetas as $t)
    <div class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">{{ $t->sistema->nombre }}</div>
                    @if($t->sistema->url_base)
                        <a href="{{ $t->sistema->url_base }}" target="_blank" rel="noopener" class="small text-muted text-decoration-none">
                            {{ preg_replace('#^https?://#', '', rtrim($t->sistema->url_base, '/')) }}
                        </a>
                    @endif
                </div>
                @if($t->sistema->activo)
                    <span class="badge text-bg-success">Activo</span>
                @else
                    <span class="badge text-bg-secondary">Inactivo</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row text-center g-2 mb-3">
                    <div class="col-3">
                        <div class="fs-4 fw-semibold">{{ $t->clientes }}</div>
                        <div class="text-muted small">Clientes</div>
                    </div>
                    <div class="col-3">
                        <div class="fs-4 fw-semibold text-success">{{ $t->activas }}</div>
                        <div class="text-muted small">Al día</div>
                    </div>
                    <div class="col-3">
                        <div class="fs-4 fw-semibold text-warning">{{ $t->pendientes }}</div>
                        <div class="text-muted small">Pendientes</div>
                    </div>
                    <div class="col-3">
                        <div class="fs-4 fw-semibold text-danger">{{ $t->vencidas }}</div>
                        <div class="text-muted small">Vencidas</div>
                    </div>
                </div>

                <ul class="list-unstyled small mb-0">
                    <li class="d-flex justify-content-between py-1 border-top">
                        <span class="text-muted">Ingreso mensual recurrente</span>
                        <strong>${{ number_format($t->mrr, 2, ',', '.') }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-top">
                        <span class="text-muted">Cobrado este mes</span>
                        <strong>${{ number_format($t->cobradoMes, 2, ',', '.') }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-top">
                        <span class="text-muted">Cobrado histórico</span>
                        <strong>${{ number_format($t->cobradoTotal, 2, ',', '.') }}</strong>
                    </li>
                    <li class="d-flex justify-content-between py-1 border-top">
                        <span class="text-muted">Próximo cobro</span>
                        <strong>{{ $t->proximo ? \Carbon\Carbon::parse($t->proximo)->format('d/m/Y') : '—' }}</strong>
                    </li>
                    @if($t->porVencer > 0)
                    <li class="d-flex justify-content-between py-1 border-top">
                        <span class="text-muted">Vencen en 7 días</span>
                        <span class="badge text-bg-warning">{{ $t->porVencer }}</span>
                    </li>
                    @endif
                    @if($t->pagosPendientes > 0)
                    <li class="d-flex justify-content-between py-1 border-top">
                        <span class="text-muted">Pagos por confirmar</span>
                        <span class="badge text-bg-info">{{ $t->pagosPendientes }}</span>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="{{ route('sistemas.show', $t->sistema) }}" class="btn btn-sm btn-outline-primary">
                    Ver detalle <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-light border">Todavía no registraste ningún sistema.
            <a href="{{ route('sistemas.create') }}">Agregar sistema</a></div>
    </div>
    @endforelse
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
