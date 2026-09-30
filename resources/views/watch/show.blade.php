@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <a href="{{ route('watch.index') }}" class="btn-ghost btn d-inline-flex align-items-center gap-1 mb-3">
    <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_catalog') }}
  </a>

  <div class="row g-4 align-items-start">
    <div class="col-md-5">
      <div class="detail-panel">
        <img src="{{ asset($watch->image ?? 'img/watches/generic.svg') }}" alt="{{ $watch->name }}">
      </div>
    </div>
    <div class="col-md-7">
      <span class="brand-pill">{{ $watch->brand }}</span>
      <h1 class="hero-title mt-2" style="font-size: 2rem;">{{ $watch->name }}</h1>
      <p class="hero-lead">{{ $watch->description }}</p>
      <p class="watch-price">${{ number_format($watch->price, 0, ',', '.') }}</p>
      <p class="mb-4">
        @if($watch->stock > 0)
          <span class="stock-badge" style="color: var(--accent)"><span class="stock-dot in"></span>{{ __('messages.available', ['count' => $watch->stock]) }}</span>
        @else
          <span class="stock-badge" style="color: var(--danger)"><span class="stock-dot out"></span>{{ __('messages.out_of_stock') }}</span>
        @endif
      </p>

      @auth
        <div class="d-flex flex-wrap align-items-center gap-2">
          @if($watch->stock > 0)
            <form method="POST" action="{{ route('cart.add', $watch) }}" class="d-flex align-items-center gap-2">
              @csrf
              <input type="number" name="quantity" value="1" min="1" max="{{ $watch->stock }}" class="form-control qty-input" />
              <button type="submit" class="btn btn-brand"><i class="bi bi-bag-plus me-1"></i>{{ __('messages.add_to_cart') }}</button>
            </form>
          @endif
          @if($inWishlist)
            <form method="POST" action="{{ route('wishlist.remove', $watch) }}">
              @csrf @method('DELETE')
              <button type="submit" class="btn-heart is-active" title="{{ __('messages.remove_from_wishlist') }}"><i class="bi bi-heart-fill"></i></button>
            </form>
          @else
            <form method="POST" action="{{ route('wishlist.add', $watch) }}">
              @csrf
              <button type="submit" class="btn-heart" title="{{ __('messages.add_to_wishlist') }}"><i class="bi bi-heart"></i></button>
            </form>
          @endif
        </div>
      @else
        <p><a href="{{ route('login') }}">{{ __('messages.nav_login') }}</a> {{ __('messages.guest_notice') }}</p>
      @endauth
    </div>
  </div>
</div>
@endsection
