@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="table-responsive">
  <table class="table">
    <thead><tr><th>Reloj</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
    <tbody>
      @foreach($order->items as $item)
        <tr>
          <td>{{ $item->watch->name }}</td>
          <td>${{ number_format($item->price, 0, ',', '.') }}</td>
          <td>{{ $item->quantity }}</td>
          <td>${{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th>${{ number_format($order->total, 0, ',', '.') }}</th></tr></tfoot>
  </table>
</div>
<a href="{{ route('watch.index') }}" class="btn bg-primary text-white">Seguir comprando</a>
@endsection
