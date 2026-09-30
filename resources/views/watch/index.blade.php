@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <div class="section-heading">
    <div>
      <h3 class="mb-1">{{ $subtitle }}</h3>
      <p>Explora por marca, estilo o simplemente déjate llevar.</p>
    </div>
  </div>

  @if($watches->isEmpty())
    <div class="empty-state">
      <i class="bi bi-search"></i>
      <h5>No se encontraron relojes.</h5>
      <p>Prueba con otra marca o palabra clave.</p>
      <a href="{{ route('watch.index') }}" class="btn btn-brand">Ver todos</a>
    </div>
  @else
    <div class="row">
      @foreach($watches as $watch)
        @include('partials.watch-card')
      @endforeach
    </div>
  @endif
</div>
@endsection
