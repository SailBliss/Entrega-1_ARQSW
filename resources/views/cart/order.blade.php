@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <div class="empty-state mb-4" style="padding-top: 1rem;">
    <i class="bi bi-bag-check" style="color: var(--accent);"></i>
    <h4>¡Gracias por tu compra!</h4>
    <p>Este es el resumen de tu pedido #{{ $order->id }}.</p>
  </div>

  <div class="card card-soft mb-3">
    <div class="table-responsive">
      <table class="table mb-0">
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
  </div>
  <a href="{{ route('watch.index') }}" class="btn btn-brand">Seguir comprando</a>
</div>
@endsection
