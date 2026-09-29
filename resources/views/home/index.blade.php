@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="text-center mb-4">
  <p class="lead">{{ __('messages.home_welcome') }}</p>
  <a href="{{ route('watch.index') }}" class="btn bg-primary text-white">{{ __('messages.home_view_all') }}</a>
</div>
<h4 class="mb-3">{{ __('messages.home_news') }}</h4>
<div class="row">
  @foreach($featured as $watch)
    @include('partials.watch-card', ['watch' => $watch, 'wishlistIds' => auth()->check() ? auth()->user()->wishlistItems()->pluck('watch_id')->all() : []])
  @endforeach
</div>
@endsection
