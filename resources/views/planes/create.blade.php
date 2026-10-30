@extends('layouts.app')

@section('content')
<h3 class="mb-3"><i class="bi bi-tags me-2"></i>Nuevo plan</h3>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('planes.store') }}">
            @include('planes._form')
        </form>
    </div>
</div>
@endsection
