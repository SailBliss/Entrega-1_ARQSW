@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="text-center mb-4">
  <p class="lead">Bienvenido a Tienda Relojes. Encuentra el reloj perfecto de las mejores marcas.</p>
  <a href="{{ route('watch.index') }}" class="btn bg-primary text-white">Ver todos los relojes</a>
</div>
<h4 class="mb-3">Novedades</h4>
<div class="row">
  @foreach($featured as $watch)
    @include('partials.watch-card', ['watch' => $watch, 'wishlistIds' => auth()->check() ? auth()->user()->wishlistItems()->pluck('watch_id')->all() : []])
  @endforeach
</div>
@endsection
