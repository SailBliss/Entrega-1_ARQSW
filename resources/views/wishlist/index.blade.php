{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <h3 class="mb-4"><i class="bi bi-heart me-2"></i>{{ $subtitle }}</h3>

  @if($items->isEmpty())
    <div class="empty-state">
      <i class="bi bi-heart"></i>
      <h5>{{ __('messages.wishlist_empty') }}</h5>
      <p>{{ __('messages.wishlist_empty_help') }}</p>
      <a href="{{ route('watch.index') }}" class="btn btn-brand">{{ __('messages.cart_go_catalog') }}</a>
    </div>
  @else
    <div class="row">
      @foreach($items as $item)
        @include('partials.watch-card', ['watch' => $item->watch, 'wishlistIds' => $items->pluck('watch_id')->all()])
      @endforeach
    </div>
  @endif
</div>
@endsection
