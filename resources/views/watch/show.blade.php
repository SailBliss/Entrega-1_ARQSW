@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="card mb-3">
  <div class="row g-0">
    <div class="col-md-4">
      <img src="{{ asset('img/watch.svg') }}" class="img-fluid rounded-start" alt="{{ $watch->name }}">
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <small class="text-muted">{{ $watch->brand }}</small>
        <h4 class="card-title">{{ $watch->name }}</h4>
        <p class="card-text">{{ $watch->description }}</p>
        <p class="card-text fs-4 fw-bold">${{ number_format($watch->price, 0, ',', '.') }}</p>
        <p class="card-text">
          @if($watch->stock > 0)
            <span class="text-success">Disponibles: {{ $watch->stock }}</span>
          @else
            <span class="text-danger">Agotado</span>
          @endif
        </p>

        @auth
          @if($watch->stock > 0)
            <form method="POST" action="{{ route('cart.add', $watch) }}" class="d-flex align-items-center mb-2">
              @csrf
              <input type="number" name="quantity" value="1" min="1" max="{{ $watch->stock }}" class="form-control me-2" style="width:90px" />
              <button type="submit" class="btn bg-primary text-white">Añadir al carrito</button>
            </form>
          @endif
          @if($inWishlist)
            <form method="POST" action="{{ route('wishlist.remove', $watch) }}">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-outline-danger">Quitar de deseados</button>
            </form>
          @else
            <form method="POST" action="{{ route('wishlist.add', $watch) }}">
              @csrf
              <button type="submit" class="btn btn-outline-secondary">Añadir a deseados</button>
            </form>
          @endif
        @else
          <p><a href="{{ route('login') }}">Inicia sesión</a> para comprar o guardar en deseados.</p>
        @endauth
      </div>
    </div>
  </div>
</div>
<a href="{{ route('watch.index') }}">&laquo; Volver al catálogo</a>
@endsection
