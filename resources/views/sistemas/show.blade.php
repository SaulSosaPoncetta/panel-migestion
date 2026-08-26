@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-hdd-network me-2"></i>{{ $sistema->nombre }}</h3>
    <a href="{{ route('sistemas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

@if(session('mostrar_credenciales'))
<div class="alert alert-warning">
    <i class="bi bi-shield-lock me-1"></i>
    Guardá estas credenciales ahora — configuralas en el <code>.env</code> de {{ $sistema->nombre }}. No se van a
    volver a mostrar completas en el listado.
</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="text-muted mb-3">Credenciales de integración</h6>
        <dl class="row mb-0">
            <dt class="col-sm-3">API Key</dt>
            <dd class="col-sm-9"><code>{{ $sistema->api_key }}</code></dd>

            <dt class="col-sm-3">Webhook Secret</dt>
            <dd class="col-sm-9"><code>{{ $sistema->webhook_secret }}</code></dd>

            <dt class="col-sm-3">Endpoint de consulta</dt>
            <dd class="col-sm-9"><code>GET {{ url('/api/estado-cliente') }}</code> con header <code>X-Api-Key</code></dd>

            <dt class="col-sm-3">Webhook configurado</dt>
            <dd class="col-sm-9">{{ $sistema->webhook_url ?? '— sin configurar —' }}</dd>
        </dl>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Referencia externa</th>
                    <th>Suscripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cs)
                <tr>
                    <td><a href="{{ route('clientes.show', $cs->cliente) }}">{{ $cs->cliente->nombre }}</a></td>
                    <td>{{ $cs->referencia_externa ?? '—' }}</td>
                    <td>{{ $cs->suscripciones->first()->plan ?? '—' }}</td>
                    <td>
                        @php($estado = $cs->suscripciones->first()->estado ?? null)
                        @if($estado === 'activa')
                            <span class="badge bg-success">Activa</span>
                        @elseif($estado)
                            <span class="badge bg-warning text-dark">{{ ucfirst($estado) }}</span>
                        @else
                            <span class="badge bg-secondary">Sin suscripción</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Todavía no hay clientes vinculados a este sistema.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $clientes->links() }}</div>
@endsection
