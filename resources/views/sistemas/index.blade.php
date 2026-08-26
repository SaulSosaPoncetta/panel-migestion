@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-hdd-network me-2"></i>Sistemas SaaS</h3>
    <a href="{{ route('sistemas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nuevo sistema
    </a>
</div>

<div class="alert alert-dark">
    <i class="bi bi-info-circle me-1"></i>
    Cada sistema (GestiónAula, GestiónComercial, o los que sumes a futuro) se conecta acá con su propia API key,
    para que este panel centralice sus clientes y cobros.
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>URL base</th>
                    <th class="text-end">Clientes vinculados</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sistemas as $sistema)
                <tr>
                    <td>{{ $sistema->nombre }}</td>
                    <td><code>{{ $sistema->slug }}</code></td>
                    <td><a href="{{ $sistema->url_base }}" target="_blank">{{ $sistema->url_base }}</a></td>
                    <td class="text-end">{{ $sistema->cliente_sistemas_count }}</td>
                    <td>
                        @if($sistema->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('sistemas.show', $sistema) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Todavía no diste de alta ningún sistema.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
