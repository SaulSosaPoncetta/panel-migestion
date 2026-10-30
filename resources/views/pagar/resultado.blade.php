@extends('layouts.publico')
@section('titulo', 'Resultado del pago')

@section('contenido')
<div class="card shadow-sm">
    <div class="card-body text-center p-4">
        @if($estado === 'exito')
            <i class="bi bi-check-circle text-success fs-1"></i>
            <h5 class="mt-2">¡Gracias! Recibimos tu pago</h5>
            <p class="text-muted">En unos instantes tu servicio queda actualizado. Si no lo ves reflejado enseguida, cerrá sesión y volvé a entrar.</p>
        @elseif($estado === 'pendiente')
            <i class="bi bi-hourglass-split text-warning fs-1"></i>
            <h5 class="mt-2">Tu pago está pendiente</h5>
            <p class="text-muted">Mercado Pago todavía lo está procesando. Cuando se acredite, tu servicio se actualiza solo.</p>
        @else
            <i class="bi bi-x-circle text-danger fs-1"></i>
            <h5 class="mt-2">No pudimos completar el pago</h5>
            <p class="text-muted">No se realizó ningún cobro. Podés volver al sistema e intentarlo de nuevo.</p>
        @endif

        @if($sistema)
            <a href="{{ $sistema->url_base }}" class="btn btn-marca mt-2">
                <i class="bi bi-arrow-left me-1"></i>Volver a {{ $sistema->nombre }}
            </a>
        @endif
    </div>
</div>
@endsection
