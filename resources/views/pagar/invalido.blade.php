@extends('layouts.publico')
@section('titulo', 'Enlace no válido')

@section('contenido')
<div class="card shadow-sm">
    <div class="card-body text-center p-4">
        <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
        <h5 class="mt-2">No pudimos abrir el pago</h5>
        <p class="text-muted mb-0">{{ $mensaje }}</p>
    </div>
</div>
@endsection
