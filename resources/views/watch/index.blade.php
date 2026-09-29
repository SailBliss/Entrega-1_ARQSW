@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
@if($watches->isEmpty())
  <p class="text-center lead">{{ __('messages.watches_not_found') }}</p>
  <div class="text-center"><a href="{{ route('watch.index') }}" class="btn bg-primary text-white">{{ __('messages.see_all') }}</a></div>
@else
  <div class="row">
    @foreach($watches as $watch)
      @include('partials.watch-card')
    @endforeach
  </div>
@endif
@endsection
