{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
@extends('layouts.app')
@section('title', $title)
@section('content')
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <p class="hero-eyebrow">{{ __('messages.home_eyebrow') }}</p>
        <h1 class="hero-title mb-3">{{ __('messages.home_hero_title') }}</h1>
        <p class="hero-lead mb-4">{{ __('messages.home_hero_lead') }}</p>
        <a href="{{ route('watch.index') }}" class="btn btn-brand">{{ __('messages.home_view_catalog') }} <i class="bi bi-arrow-right ms-1"></i></a>
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
        <h5>{{ __('messages.home_feature_curated') }}</h5>
        <p class="text-muted mb-0">{{ __('messages.home_feature_curated_text') }}</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="feature">
        <div class="feature-icon"><i class="bi bi-truck"></i></div>
        <h5>{{ __('messages.home_feature_shipping') }}</h5>
        <p class="text-muted mb-0">{{ __('messages.home_feature_shipping_text') }}</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="feature">
        <div class="feature-icon"><i class="bi bi-headset"></i></div>
        <h5>{{ __('messages.home_feature_support') }}</h5>
        <p class="text-muted mb-0">{{ __('messages.home_feature_support_text') }}</p>
      </div>
    </div>
  </div>

  <div class="section-heading mt-5">
    <div>
      <h3 class="mb-1">{{ __('messages.home_news') }}</h3>
      <p>{{ __('messages.home_news_text') }}</p>
    </div>
    <a href="{{ route('watch.index') }}" class="btn btn-ghost">{{ __('messages.home_view_all_short') }}</a>
  </div>
  <div class="row mb-5">
    @foreach($featured as $watch)
      @include('partials.watch-card', ['watch' => $watch, 'wishlistIds' => auth()->check() ? auth()->user()->wishlistItems()->pluck('watch_id')->all() : []])
    @endforeach
  </div>
</div>
@endsection
