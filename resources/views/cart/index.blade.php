@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
@if($items->isEmpty())
  <p class="text-center lead">Tu carrito está vacío.</p>
  <div class="text-center"><a href="{{ route('watch.index') }}" class="btn bg-primary text-white">Ir al catálogo</a></div>
@else
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Reloj</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
      </thead>
      <tbody>
        @foreach($items as $item)
          <tr>
            <td><a href="{{ route('watch.show', $item->watch) }}">{{ $item->watch->name }}</a></td>
            <td>${{ number_format($item->watch->price, 0, ',', '.') }}</td>
            <td>{{ $item->quantity }}</td>
            <td>${{ number_format($item->subtotal(), 0, ',', '.') }}</td>
            <td>
              <form method="POST" action="{{ route('cart.remove', $item->watch) }}">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th id="cart-total">${{ number_format($total, 0, ',', '.') }}</th><th></th></tr>
      </tfoot>
    </table>
  </div>
  <div class="d-flex gap-2">
    <form method="POST" action="{{ route('cart.clear') }}">
      @csrf @method('DELETE')
      <button class="btn btn-outline-secondary" type="submit">Vaciar carrito</button>
    </form>
    <form method="POST" action="{{ route('cart.checkout') }}">
      @csrf
      <button class="btn bg-primary text-white" type="submit">Comprar</button>
    </form>
  </div>
@endif
@endsection
