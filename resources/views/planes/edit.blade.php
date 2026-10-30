@extends('layouts.app')

@section('content')
<h3 class="mb-3"><i class="bi bi-tags me-2"></i>Editar plan</h3>
<div class="alert alert-info small">
    Si cambiás el precio, las suscripciones que ya existen mantienen el monto que tenían. El nuevo precio se aplica a las que se creen desde ahora.
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('planes.update', $plan) }}">
            @method('PUT')
            @include('planes._form')
        </form>
    </div>
</div>
@endsection
