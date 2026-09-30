@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <div class="section-heading">
    <div>
      <h3 class="mb-1">{{ $subtitle }}</h3>
      <p>{{ __('messages.watches_explore') }}</p>
    </div>
  </div>

  @if($watches->isEmpty())
    <div class="empty-state">
      <i class="bi bi-search"></i>
      <h5>{{ __('messages.watches_not_found') }}</h5>
      <p>{{ __('messages.watches_not_found_help') }}</p>
      <a href="{{ route('watch.index') }}" class="btn btn-brand">{{ __('messages.see_all') }}</a>
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
