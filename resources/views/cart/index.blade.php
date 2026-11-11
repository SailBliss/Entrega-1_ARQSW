{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
@extends('layouts.app')
@section('title', $title)
@section('content')
@inject('prices', 'App\Services\PriceFormatter')
<div class="container my-4">
  <h3 class="mb-4"><i class="bi bi-bag me-2"></i>{{ $subtitle }}</h3>

  @if($items->isEmpty())
    <div class="empty-state">
      <i class="bi bi-bag"></i>
      <h5>{{ __('messages.cart_empty') }}</h5>
      <p>{{ __('messages.cart_empty_help') }}</p>
      <a href="{{ route('watch.index') }}" class="btn btn-brand">{{ __('messages.cart_go_catalog') }}</a>
    </div>
  @else
    {{-- Promo Hint for Single Watch --}}
    @if(($totalQuantity ?? 0) === 1)
      <div class="alert alert-info d-flex align-items-center mb-3 p-3 shadow-sm border-0 border-start border-4 border-info">
        <i class="bi bi-gift-fill fs-3 text-info me-3"></i>
        <div>
          <div class="fw-bold">{{ __('messages.cart_plant_promo_banner') }}</div>
          <div class="small text-muted">{{ __('messages.cart_plant_promo_hint') }}</div>
        </div>
      </div>
    @endif

    {{-- Promo Achieved Banner --}}
    @if(!empty($giftPlant))
      <div class="alert alert-success d-flex align-items-center mb-3 p-3 shadow-sm border-0 border-start border-4 border-success">
        <i class="bi bi-patch-check-fill fs-3 text-success me-3"></i>
        <div>
          <div class="fw-bold text-success">🌿 ¡Calificas para una Planta de Regalo!</div>
          <div class="small text-muted">Por comprar 2 o más relojes, hemos añadido <strong>{{ $giftPlant['nombre'] }}</strong> totalmente gratis a tu carrito.</div>
        </div>
      </div>
    @endif

    <div class="card card-soft mb-3">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ __('messages.cart_col_watch') }}</th>
              <th>{{ __('messages.cart_col_price') }}</th>
              <th>{{ __('messages.cart_col_quantity') }}</th>
              <th>{{ __('messages.cart_col_subtotal') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              <tr>
                <td>
                  <a href="{{ route('watch.show', $item->watch) }}" class="text-reset fw-semibold">{{ $item->watch->name }}</a>
                  <div class="text-muted small">{{ $item->watch->brand }}</div>
                </td>
                <td>{{ $prices->format($item->watch->price) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $prices->format($item->subtotal()) }}</td>
                <td>
                  <form method="POST" action="{{ route('cart.remove', $item->watch) }}">
                    @csrf @method('DELETE')
                    <button class="btn-ghost btn btn-sm" type="submit" title="{{ __('messages.cart_btn_remove') }}"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach

            {{-- Plant Gift Item Row --}}
            @if(!empty($giftPlant))
              <tr class="table-success-subtle border-top border-2 border-success-subtle">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    @if(!empty($giftPlant['imagen_url']))
                      <img src="{{ $giftPlant['imagen_url'] }}" alt="{{ $giftPlant['nombre'] }}" class="rounded border shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
                    @else
                      <div class="p-2 bg-success-subtle rounded"><i class="bi bi-flower1 fs-4 text-success"></i></div>
                    @endif
                    <div>
                      <span class="badge bg-success mb-1"><i class="bi bi-gift-fill me-1"></i> Regalo Promocional</span>
                      <div class="fw-bold text-success">{{ $giftPlant['nombre'] }}</div>
                      <small class="text-muted">{{ $giftPlant['categoria'] ?? 'Planta' }} · {{ __('messages.cart_plant_partner_credit') }}</small>
                      @if(!empty($giftPlant['detalle_url']))
                        <div><a href="{{ $giftPlant['detalle_url'] }}" target="_blank" class="small text-decoration-none text-success"><i class="bi bi-box-arrow-up-right me-1"></i>Ver en tienda de plantas</a></div>
                      @endif
                    </div>
                  </div>
                </td>
                <td>
                  @if(!empty($giftPlant['precio']))
                    <div class="text-decoration-line-through text-muted small">${{ number_format($giftPlant['precio'], 0, ',', '.') }}</div>
                  @endif
                  <span class="fw-bold text-success">{{ __('messages.cart_plant_gift_free') }}</span>
                </td>
                <td>1</td>
                <td class="fw-bold text-success">$0</td>
                <td>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">GRATIS</span>
                </td>
              </tr>
            @endif
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">{{ __('messages.cart_col_total') }}</th>
              <th id="cart-total">{{ $prices->format($total) }}</th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex gap-2">
        <form method="POST" action="{{ route('cart.clear') }}">
          @csrf @method('DELETE')
          <button class="btn btn-outline-brand" type="submit">{{ __('messages.cart_btn_clear') }}</button>
        </form>
        <form method="POST" action="{{ route('cart.checkout') }}">
          @csrf
          <button class="btn btn-brand" type="submit"><i class="bi bi-check2-circle me-1"></i>{{ __('messages.cart_btn_checkout') }}</button>
        </form>
      </div>
      <a href="{{ route('watch.index') }}" class="btn btn-ghost">« Seguir viendo relojes</a>
    </div>
  @endif
</div>
@endsection
