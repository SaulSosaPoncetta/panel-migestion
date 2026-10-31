@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Pagos</h3>
</div>

@if($porRevisar > 0)
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <span><i class="bi bi-hourglass-split me-1"></i>
            Tenés <strong>{{ $porRevisar }}</strong> {{ $porRevisar === 1 ? 'transferencia' : 'transferencias' }} con comprobante esperando tu revisión.</span>
        <a href="{{ route('pagos.index', ['estado' => 'pendiente', 'medio' => 'transferencia']) }}" class="btn btn-sm btn-warning">Ver</a>
    </div>
@endif

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="estado" class="form-select" onchange="this.form.submit()">
            <option value="">Todos los estados</option>
            @foreach(['pendiente', 'aprobado', 'rechazado', 'reembolsado'] as $e)
                <option value="{{ $e }}" @selected(request('estado') === $e)>{{ ucfirst($e) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="medio" class="form-select" onchange="this.form.submit()">
            <option value="">Todos los medios</option>
            <option value="mercadopago" @selected(request('medio') === 'mercadopago')>Mercado Pago</option>
            <option value="transferencia" @selected(request('medio') === 'transferencia')>Transferencia</option>
        </select>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Sistema / plan</th>
                    <th class="text-end">Monto</th>
                    <th>Medio</th>
                    <th>Estado</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                @php($cs = $pago->suscripcion->clienteSistema)
                <tr>
                    <td>{{ $pago->id_pago }}</td>
                    <td>{{ ($pago->fecha_pago ?? $pago->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $cs->cliente->nombre }}</td>
                    <td>{{ $cs->sistema->nombre }}<div class="text-muted small">{{ $pago->suscripcion->plan }}</div></td>
                    <td class="text-end">{{ $pago->moneda }} {{ number_format($pago->monto, 2, ',', '.') }}</td>
                    <td>
                        {{ $pago->esTransferencia() ? 'Transferencia' : 'Mercado Pago' }}
                        @if($pago->comprobante_path)<i class="bi bi-paperclip" title="Tiene comprobante"></i>@endif
                    </td>
                    <td>
                        @switch($pago->estado)
                            @case('aprobado') <span class="badge bg-success">Aprobado</span> @break
                            @case('rechazado') <span class="badge bg-danger">Rechazado</span> @break
                            @case('reembolsado') <span class="badge bg-secondary">Reembolsado</span> @break
                            @default
                                @if($pago->esTransferencia() && $pago->comprobante_path)
                                    <span class="badge bg-warning text-dark">Por revisar</span>
                                @else
                                    <span class="badge bg-light text-dark border">Pendiente</span>
                                @endif
                        @endswitch
                    </td>
                    <td class="text-end">
                        <a href="{{ route('pagos.show', $pago) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Ver</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No hay pagos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pagos->hasPages())
        <div class="card-footer bg-white">{{ $pagos->links() }}</div>
    @endif
</div>
@endsection
