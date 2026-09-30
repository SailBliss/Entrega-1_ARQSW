@extends('layouts.app')
@section('title', $title)
@section('content')
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <p class="hero-eyebrow">Piezas que cuentan tu historia</p>
        <h1 class="hero-title mb-3">Encuentra el reloj que se siente como tuyo</h1>
        <p class="hero-lead mb-4">Curamos relojes de marcas que confían en la mecánica bien hecha, para acompañarte en cada momento que decidas medir.</p>
        <a href="{{ route('watch.index') }}" class="btn btn-brand">Ver el catálogo <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
      <div class="col-lg-6">
        <div class="hero-art">
          <img src="{{ asset('img/watches/tissot-prx-powermatic80.svg') }}" alt="" />
          <img src="{{ asset('img/watches/orient-bambino-v2.svg') }}" alt="" />
        </div>
      </div>
    </div>
  </div>
</section>

<div class="container">
  <div class="row text-center g-4 my-2">
    <div class="col-md-4">
      <div class="feature">
        <div class="feature-icon"><i class="bi bi-hand-thumbs-up"></i></div>
        <h5>Curado a mano</h5>
        <p class="text-muted mb-0">Cada reloj pasa por nuestras manos antes de llegar a las tuyas.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="feature">
        <div class="feature-icon"><i class="bi bi-truck"></i></div>
        <h5>Envío cuidadoso</h5>
        <p class="text-muted mb-0">Empacamos cada pieza como si fuera para alguien que queremos.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="feature">
        <div class="feature-icon"><i class="bi bi-headset"></i></div>
        <h5>Te acompañamos</h5>
        <p class="text-muted mb-0">Si tienes dudas, hay una persona real lista para ayudarte.</p>
      </div>
    </div>
  </div>

  <div class="section-heading mt-5">
    <div>
      <h3 class="mb-1">Novedades</h3>
      <p>Lo último que sumamos a la colección.</p>
    </div>
    <a href="{{ route('watch.index') }}" class="btn btn-ghost">Ver todo</a>
  </div>
  <div class="row mb-5">
    @foreach($featured as $watch)
      @include('partials.watch-card', ['watch' => $watch, 'wishlistIds' => auth()->check() ? auth()->user()->wishlistItems()->pluck('watch_id')->all() : []])
    @endforeach
  </div>
</div>
@endsection
