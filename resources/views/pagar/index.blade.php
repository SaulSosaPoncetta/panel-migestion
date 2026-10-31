@extends('layouts.publico')
@section('titulo', 'Pagar '.$sistema->nombre)

@section('contenido')
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-marca text-white">
        <div class="small opacity-75">Estás pagando</div>
        <div class="fs-5 fw-semibold">{{ $sistema->nombre }}</div>
    </div>
    <div class="card-body p-4">
        <div class="mb-3">
            <div class="text-muted small">Cuenta</div>
            <div class="fw-semibold">{{ $cliente->nombre }}</div>
            <div class="text-muted small">{{ $cliente->email }}</div>
        </div>

        @if($suscripcion)
            <div class="alert alert-light border small">
                <strong>Estado actual:</strong>
                @switch($suscripcion->estado)
                    @case('activa') <span class="text-success">al día</span> @break
                    @case('vencida') <span class="text-danger">vencido</span> @break
                    @case('suspendida') <span class="text-danger">suspendido</span> @break
                    @default <span class="text-warning">pendiente de pago</span>
                @endswitch
                @if($suscripcion->fecha_vencimiento)
                    · vence el {{ $suscripcion->fecha_vencimiento->format('d/m/Y') }}
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('pagar.iniciar') }}?{{ $queryFirmada }}" id="form-pago">
            @csrf

            @foreach($planes as $i => $plan)
                <label class="plan-opcion card mb-2 border">
                    <div class="card-body py-3 d-flex align-items-start">
                        @if($planes->count() > 1)
                            <input class="form-check-input mt-1 me-3" type="radio" name="id_plan" value="{{ $plan->id_plan }}"
                                   @checked(old('id_plan', $i === 0 ? $plan->id_plan : null) == $plan->id_plan)>
                        @else
                            <input type="hidden" name="id_plan" value="{{ $plan->id_plan }}">
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $plan->nombre }}</div>
                            @if($plan->descripcion)<div class="text-muted small">{{ $plan->descripcion }}</div>@endif
                            <div class="text-muted small">{{ $plan->periodoTexto() }}{{ $plan->tipo === 'recurrente' ? ' · débito automático' : '' }}</div>
                        </div>
                        <div class="text-end ms-3">
                            <div class="fw-semibold">{{ $plan->moneda }} {{ number_format($plan->precio, 2, ',', '.') }}</div>
                            @if($plan->cobro_por_unidad)
                                <div class="text-muted small">por {{ $plan->unidad_nombre ?: 'unidad' }}</div>
                            @endif
                        </div>
                    </div>
                    <span class="d-none datos-plan"
                          data-id="{{ $plan->id_plan }}"
                          data-precio="{{ $plan->precio }}"
                          data-moneda="{{ $plan->moneda }}"
                          data-por-unidad="{{ $plan->cobro_por_unidad ? 1 : 0 }}"
                          data-unidad="{{ $plan->unidad_nombre ?: 'unidades' }}"
                          data-min="{{ $plan->unidad_minima }}"
                          data-max="{{ $plan->unidad_maxima }}"></span>
                </label>
            @endforeach

            <div class="mb-3 mt-3" id="grupo-cantidad" style="display:none;">
                <label class="form-label" for="cantidad" id="label-cantidad">Cantidad</label>
                <input type="number" min="1" class="form-control" name="cantidad" id="cantidad" value="{{ old('cantidad', $cantidadInicial ?: '') }}">
                <div class="form-text" id="ayuda-cantidad"></div>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                <span class="text-muted">Total a pagar</span>
                <span class="fs-4 fw-semibold" id="total">—</span>
            </div>

            <button type="submit" name="medio" value="mercadopago" class="btn btn-marca btn-lg w-100 mt-3">
                <i class="bi bi-credit-card me-1"></i>Pagar con Mercado Pago
            </button>
            @if($transferenciaActiva)
                <button type="submit" name="medio" value="transferencia" class="btn btn-outline-secondary btn-lg w-100 mt-2">
                    <i class="bi bi-bank me-1"></i>Pagar por transferencia bancaria
                </button>
                <div class="form-text text-center">Con transferencia, tu servicio se activa cuando acreditamos el pago.</div>
            @endif
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('form-pago');
    const grupo = document.getElementById('grupo-cantidad');
    const cantidad = document.getElementById('cantidad');
    const total = document.getElementById('total');
    const ayuda = document.getElementById('ayuda-cantidad');
    const etiqueta = document.getElementById('label-cantidad');

    function planElegido() {
        const radio = form.querySelector('input[name="id_plan"]:checked');
        const id = radio ? radio.value : form.querySelector('input[name="id_plan"]').value;
        return form.querySelector('.datos-plan[data-id="' + id + '"]').dataset;
    }

    function actualizar() {
        const p = planElegido();
        const porUnidad = p.porUnidad === '1';
        grupo.style.display = porUnidad ? '' : 'none';
        let n = 1;
        if (porUnidad) {
            const min = parseInt(p.min || '1', 10);
            const max = p.max ? parseInt(p.max, 10) : null;
            etiqueta.textContent = 'Cantidad de ' + p.unidad;
            ayuda.textContent = max ? 'Entre ' + min + ' y ' + max + '.' : 'Mínimo ' + min + '.';
            cantidad.min = min;
            if (max) cantidad.max = max;
            if (!cantidad.value) cantidad.value = min;
            n = Math.max(parseInt(cantidad.value || '0', 10), 0);
        }
        const monto = parseFloat(p.precio) * n;
        total.textContent = p.moneda + ' ' + monto.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    form.addEventListener('input', actualizar);
    form.addEventListener('change', actualizar);
    actualizar();
})();
</script>
@endsection
