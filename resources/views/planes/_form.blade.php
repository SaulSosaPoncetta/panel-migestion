@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sistema</label>
        <select name="id_sistema" class="form-select @error('id_sistema') is-invalid @enderror" required>
            <option value="">Elegí un sistema…</option>
            @foreach($sistemas as $s)
                <option value="{{ $s->id_sistema }}" @selected(old('id_sistema', $plan->id_sistema) == $s->id_sistema)>{{ $s->nombre }}</option>
            @endforeach
        </select>
        @error('id_sistema')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Nombre del plan</label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $plan->nombre) }}" placeholder="Ej: Docente individual" required>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Código</label>
        <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror"
               value="{{ old('codigo', $plan->codigo) }}" placeholder="Ej: unipersonal" required>
        <div class="form-text">Es el identificador que usan los sistemas en el enlace de pago. Sin espacios.</div>
        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Descripción (opcional)</label>
        <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion', $plan->descripcion) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Tipo de cobro</label>
        <select name="tipo" class="form-select">
            <option value="recurrente" @selected(old('tipo', $plan->tipo) === 'recurrente')>Recurrente (se renueva)</option>
            <option value="unico" @selected(old('tipo', $plan->tipo) === 'unico')>Pago único</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Cada pago cubre (meses)</label>
        <input type="number" name="duracion_meses" min="1" max="120"
               class="form-control @error('duracion_meses') is-invalid @enderror"
               value="{{ old('duracion_meses', $plan->duracion_meses) }}">
        <div class="form-text">1 = mensual, 12 = anual. Vacío = sin vencimiento.</div>
        @error('duracion_meses')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Precio</label>
        <input type="number" step="0.01" min="0" name="precio" class="form-control @error('precio') is-invalid @enderror"
               value="{{ old('precio', $plan->precio) }}" required>
        @error('precio')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Moneda</label>
        <input type="text" name="moneda" maxlength="3" class="form-control" value="{{ old('moneda', $plan->moneda ?? 'ARS') }}" required>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="cobro_por_unidad" name="cobro_por_unidad" value="1"
                   @checked(old('cobro_por_unidad', $plan->cobro_por_unidad))>
            <label class="form-check-label" for="cobro_por_unidad">
                El precio es por unidad (ej: por docente). El total es precio × cantidad.
            </label>
        </div>
    </div>

    <div class="col-md-4 campo-unidad">
        <label class="form-label">Nombre de la unidad</label>
        <input type="text" name="unidad_nombre" class="form-control" value="{{ old('unidad_nombre', $plan->unidad_nombre) }}" placeholder="docente">
    </div>
    <div class="col-md-4 campo-unidad">
        <label class="form-label">Mínimo</label>
        <input type="number" min="1" name="unidad_minima" class="form-control" value="{{ old('unidad_minima', $plan->unidad_minima ?? 1) }}">
    </div>
    <div class="col-md-4 campo-unidad">
        <label class="form-label">Máximo (opcional)</label>
        <input type="number" min="1" name="unidad_maxima" class="form-control @error('unidad_maxima') is-invalid @enderror"
               value="{{ old('unidad_maxima', $plan->unidad_maxima) }}">
        @error('unidad_maxima')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                   @checked(old('activo', $plan->activo ?? true))>
            <label class="form-check-label" for="activo">Plan activo (disponible para contratar)</label>
        </div>
    </div>
</div>

<div class="mt-4">
    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar</button>
    <a href="{{ route('planes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>

<script>
    (function () {
        const chk = document.getElementById('cobro_por_unidad');
        const campos = document.querySelectorAll('.campo-unidad');
        function mostrar() { campos.forEach(c => c.style.display = chk.checked ? '' : 'none'); }
        chk.addEventListener('change', mostrar);
        mostrar();
    })();
</script>
