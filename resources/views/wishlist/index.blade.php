@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
@if($items->isEmpty())
  <p class="text-center lead">{{ __('messages.wishlist_empty') }}</p>
  <div class="text-center"><a href="{{ route('watch.index') }}" class="btn bg-primary text-white">{{ __('messages.cart_go_catalog') }}</a></div>
@else
  <div class="row">
    @foreach($items as $item)
      @include('partials.watch-card', ['watch' => $item->watch, 'wishlistIds' => $items->pluck('watch_id')->all()])
    @endforeach
  </div>
@endif
@endsection
