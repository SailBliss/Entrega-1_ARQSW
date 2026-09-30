@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <h3 class="mb-4"><i class="bi bi-bag me-2"></i>{{ $subtitle }}</h3>

  @if($items->isEmpty())
    <div class="empty-state">
      <i class="bi bi-bag"></i>
      <h5>Tu carrito está vacío.</h5>
      <p>Explora el catálogo y encuentra algo que te guste.</p>
      <a href="{{ route('watch.index') }}" class="btn btn-brand">Ir al catálogo</a>
    </div>
  @else
    <div class="card card-soft mb-3">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr><th>Reloj</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              <tr>
                <td><a href="{{ route('watch.show', $item->watch) }}" class="text-reset">{{ $item->watch->name }}</a></td>
                <td>${{ number_format($item->watch->price, 0, ',', '.') }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->subtotal(), 0, ',', '.') }}</td>
                <td>
                  <form method="POST" action="{{ route('cart.remove', $item->watch) }}">
                    @csrf @method('DELETE')
                    <button class="btn-ghost btn btn-sm" type="submit"><i class="bi bi-trash"></i></button>
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
    </div>
    <div class="d-flex gap-2">
      <form method="POST" action="{{ route('cart.clear') }}">
        @csrf @method('DELETE')
        <button class="btn btn-outline-brand" type="submit">Vaciar carrito</button>
      </form>
      <form method="POST" action="{{ route('cart.checkout') }}">
        @csrf
        <button class="btn btn-brand" type="submit"><i class="bi bi-check2-circle me-1"></i>Comprar</button>
      </form>
    </div>
  @endif
</div>
@endsection
