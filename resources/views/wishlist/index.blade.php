@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
@if($items->isEmpty())
  <p class="text-center lead">Tu lista de deseados está vacía.</p>
  <div class="text-center"><a href="{{ route('watch.index') }}" class="btn bg-primary text-white">Ir al catálogo</a></div>
@else
  <div class="row">
    @foreach($items as $item)
      @include('partials.watch-card', ['watch' => $item->watch, 'wishlistIds' => $items->pluck('watch_id')->all()])
    @endforeach
  </div>
@endif
@endsection
