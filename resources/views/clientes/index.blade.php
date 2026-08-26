@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-people me-2"></i>Clientes</h3>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nuevo cliente
    </a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('clientes.index') }}" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o email..."
                   value="{{ request('buscar') }}">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
            @if(request('buscar'))
            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th class="text-end">Sistemas vinculados</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->email }}</td>
                    <td class="text-end">{{ $cliente->cliente_sistemas_count }}</td>
                    <td>
                        @if($cliente->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No hay clientes registrados todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $clientes->appends(request()->query())->links() }}
</div>
@endsection
