@extends('layouts.app')

@section('content')
<h3 class="mb-3"><i class="bi bi-plus-lg me-2"></i>Nuevo cliente</h3>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="{{ route('clientes.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                @error('nombre') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">CUIT / DNI</label>
                <input type="text" name="identificacion_fiscal" class="form-control" value="{{ old('identificacion_fiscal') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Crear cliente
            </button>
        </form>
    </div>
</div>
@endsection
