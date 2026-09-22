@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
@if($watches->isEmpty())
  <p class="text-center lead">No se encontraron relojes.</p>
  <div class="text-center"><a href="{{ route('watch.index') }}" class="btn bg-primary text-white">Ver todos</a></div>
@else
  <div class="row">
    @foreach($watches as $watch)
      @include('partials.watch-card')
    @endforeach
  </div>
@endif
@endsection
