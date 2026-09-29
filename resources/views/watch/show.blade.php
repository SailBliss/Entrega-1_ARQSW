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
            <span class="text-success">{{ __('messages.available', ['count' => $watch->stock]) }}</span>
          @else
            <span class="text-danger">{{ __('messages.out_of_stock') }}</span>
          @endif
        </p>

        @auth
          @if($watch->stock > 0)
            <form method="POST" action="{{ route('cart.add', $watch) }}" class="d-flex align-items-center mb-2">
              @csrf
              <input type="number" name="quantity" value="1" min="1" max="{{ $watch->stock }}" class="form-control me-2" style="width:90px" />
              <button type="submit" class="btn bg-primary text-white">{{ __('messages.add_to_cart') }}</button>
            </form>
          @endif
          @if($inWishlist)
            <form method="POST" action="{{ route('wishlist.remove', $watch) }}">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-outline-danger">{{ __('messages.remove_from_wishlist') }}</button>
            </form>
          @else
            <form method="POST" action="{{ route('wishlist.add', $watch) }}">
              @csrf
              <button type="submit" class="btn btn-outline-secondary">{{ __('messages.add_to_wishlist') }}</button>
            </form>
          @endif
        @else
          <p><a href="{{ route('login') }}">{{ __('messages.nav_login') }}</a> {{ __('messages.guest_notice') }}</p>
        @endauth
      </div>
    </div>
  </div>
</div>
<a href="{{ route('watch.index') }}">{{ __('messages.back_to_catalog') }}</a>
@endsection
