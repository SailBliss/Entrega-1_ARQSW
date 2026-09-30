@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <div class="empty-state mb-4" style="padding-top: 1rem;">
    <i class="bi bi-bag-check" style="color: var(--accent);"></i>
    <h4>{{ __('messages.order_thanks') }}</h4>
    <p>{{ __('messages.order_summary', ['id' => $order->id]) }}</p>
  </div>

  <div class="card card-soft mb-3">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>{{ __('messages.cart_col_watch') }}</th><th>{{ __('messages.cart_col_price') }}</th><th>{{ __('messages.cart_col_quantity') }}</th><th>{{ __('messages.cart_col_subtotal') }}</th></tr></thead>
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
        <tfoot><tr><th colspan="3" class="text-end">{{ __('messages.cart_col_total') }}</th><th>${{ number_format($order->total, 0, ',', '.') }}</th></tr></tfoot>
      </table>
    </div>
  </div>
  <a href="{{ route('watch.index') }}" class="btn btn-brand">{{ __('messages.order_continue_shopping') }}</a>
</div>
@endsection
