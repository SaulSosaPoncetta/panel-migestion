@extends('layouts.app')

@section('content')
<h3 class="mb-3"><i class="bi bi-plus-lg me-2"></i>Nuevo sistema SaaS</h3>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="{{ route('sistemas.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}"
                       placeholder="Ej: GestiónAula" required>
                @error('nombre') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Slug (identificador corto, sin espacios)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}"
                       placeholder="Ej: gestionaula" required>
                @error('slug') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">URL base del sistema</label>
                <input type="url" name="url_base" class="form-control" value="{{ old('url_base') }}"
                       placeholder="https://gestionaula.migestion.com.ar" required>
                @error('url_base') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Webhook URL (opcional, endpoint que recibe el aviso de cambio de estado)</label>
                <input type="url" name="webhook_url" class="form-control" value="{{ old('webhook_url') }}"
                       placeholder="https://gestionaula.migestion.com.ar/webhooks/estado-cliente">
                @error('webhook_url') <div class="text-danger small">{{ $message }}</div> @enderror
                <div class="form-text">Se puede completar más adelante, no es obligatorio para empezar.</div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Crear sistema
            </button>
        </form>
    </div>
</div>
@endsection
