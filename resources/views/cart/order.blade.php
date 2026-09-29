@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="table-responsive">
  <table class="table">
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
<a href="{{ route('watch.index') }}" class="btn bg-primary text-white">{{ __('messages.order_continue_shopping') }}</a>
@endsection
