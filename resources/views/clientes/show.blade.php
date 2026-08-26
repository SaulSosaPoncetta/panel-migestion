@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-person me-2"></i>{{ $cliente->nombre }}</h3>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

@if(session('link_pago'))
<div class="alert alert-success">
    <i class="bi bi-link-45deg me-1"></i>
    Link de cobro generado — compartíselo al cliente:
    <a href="{{ session('link_pago') }}" target="_blank">{{ session('link_pago') }}</a>
</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-2">Email</dt>
            <dd class="col-sm-10">{{ $cliente->email }}</dd>
            <dt class="col-sm-2">Teléfono</dt>
            <dd class="col-sm-10">{{ $cliente->telefono ?? '—' }}</dd>
            <dt class="col-sm-2">CUIT/DNI</dt>
            <dd class="col-sm-10">{{ $cliente->identificacion_fiscal ?? '—' }}</dd>
        </dl>
    </div>
</div>

{{-- Vincular un nuevo sistema --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white"><i class="bi bi-plus-lg me-1"></i>Vincular a un sistema</div>
    <div class="card-body">
        <form method="POST" action="{{ route('clientes.sistemas.store', $cliente) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small">Sistema</label>
                <select name="id_sistema" class="form-select" required>
                    <option value="">Elegir...</option>
                    @foreach($sistemas as $sistema)
                        <option value="{{ $sistema->id_sistema }}">{{ $sistema->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small">Referencia externa (id de usuario/empresa en ese sistema)</label>
                <input type="text" name="referencia_externa" class="form-control" placeholder="Opcional">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Vincular</button>
            </div>
        </form>
    </div>
</div>

{{-- Cuentas vinculadas, suscripciones y cobro --}}
@forelse($cliente->clienteSistemas as $cs)
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-hdd-network me-1"></i>{{ $cs->sistema->nombre }}</span>
        <form action="{{ route('clientes.sistemas.destroy', [$cliente, $cs]) }}" method="POST"
              onsubmit="return confirm('¿Desvincular este sistema del cliente?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Desvincular</button>
        </form>
    </div>
    <div class="card-body">
        @if($cs->suscripciones->isEmpty())
            <form method="POST" action="{{ route('suscripciones.store', $cs) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small">Plan</label>
                    <input type="text" name="plan" class="form-control" placeholder="Ej: Mensual" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="recurrente">Recurrente</option>
                        <option value="unico">Único</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Monto</label>
                    <input type="number" step="0.01" name="monto" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Moneda</label>
                    <input type="text" name="moneda" class="form-control" value="ARS" maxlength="3">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Crear suscripción</button>
                </div>
            </form>
        @else
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr><th>Plan</th><th>Tipo</th><th>Monto</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody>
                @foreach($cs->suscripciones as $suscripcion)
                    <tr>
                        <td>{{ $suscripcion->plan }}</td>
                        <td>{{ ucfirst($suscripcion->tipo) }}</td>
                        <td>{{ $suscripcion->moneda }} {{ number_format($suscripcion->monto, 2) }}</td>
                        <td>
                            @php($badge = ['activa' => 'success', 'pendiente' => 'secondary', 'vencida' => 'warning', 'suspendida' => 'danger', 'cancelada' => 'dark'][$suscripcion->estado] ?? 'secondary')
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($suscripcion->estado) }}</span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('suscripciones.cobrar', $suscripcion) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-credit-card"></i> Generar cobro (MP)
                                </button>
                            </form>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown">Estado</button>
                                <ul class="dropdown-menu">
                                    @foreach(['activa', 'vencida', 'suspendida', 'cancelada'] as $estado)
                                    <li>
                                        <form action="{{ route('suscripciones.estado', $suscripcion) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="estado" value="{{ $estado }}">
                                            <button type="submit" class="dropdown-item">{{ ucfirst($estado) }}</button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@empty
<div class="alert alert-light border text-muted">Este cliente todavía no está vinculado a ningún sistema.</div>
@endforelse
@endsection
