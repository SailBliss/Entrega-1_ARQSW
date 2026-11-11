{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
@extends('layouts.app')
@section('title', $title)
@section('content')
@inject('prices', 'App\Services\PriceFormatter')
<div class="container my-4">
  <div class="empty-state mb-4" style="padding-top: 1rem;">
    <i class="bi bi-bag-check" style="color: var(--accent);"></i>
    <h4>{{ __('messages.order_thanks') }}</h4>
    <p>{{ __('messages.order_summary', ['id' => $order->id]) }}</p>
  </div>

  {{-- Order Items Table --}}
  <div class="card card-soft mb-4">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr>
            <th>{{ __('messages.cart_col_watch') }}</th>
            <th>{{ __('messages.cart_col_price') }}</th>
            <th>{{ __('messages.cart_col_quantity') }}</th>
            <th>{{ __('messages.cart_col_subtotal') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $item)
            <tr>
              <td>
                <div class="fw-semibold">{{ $item->watch->name }}</div>
                <div class="text-muted small">{{ $item->watch->brand }}</div>
                @if($item->certificate)
                  <div class="mt-1">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                      <i class="bi bi-shield-check"></i> {{ __('messages.cert_number', ['hash' => substr($item->certificate->certificate_hash, 0, 8)]) }}...
                    </span>
                  </div>
                @endif
              </td>
              <td>{{ $prices->format($item->price) }}</td>
              <td>{{ $item->quantity }}</td>
              <td>{{ $prices->format($item->price, $item->quantity) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">{{ __('messages.cart_col_total') }}</th>
            <th>{{ $prices->format($order->total) }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Plant Gift Included in Order --}}
  @if($order->hasGiftPlant())
    <div class="mb-4">
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-flower1 fs-3 text-success"></i>
        <h5 class="mb-0 fw-bold text-success">{{ __('messages.order_gift_plant_heading') }}</h5>
      </div>
      <div class="card card-soft p-3 border-start border-4 border-success shadow-sm">
        <div class="row align-items-center">
        <div class="col-md-2 text-center mb-3 mb-md-0">
          @if($order->gift_plant_image)
            <img src="{{ $order->gift_plant_image }}" alt="{{ $order->gift_plant_name }}" class="img-fluid rounded border shadow-sm" style="max-height: 85px; object-fit: cover;">
          @else
            <div class="p-3 bg-success-subtle rounded"><i class="bi bi-flower1 fs-1 text-success"></i></div>
          @endif
        </div>
        <div class="col-md-7">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-success"><i class="bi bi-gift-fill me-1"></i> Obsequio por compra 2+</span>
            @if($order->gift_plant_category)
              <span class="badge bg-light text-dark border">{{ $order->gift_plant_category }}</span>
            @endif
          </div>
          <h5 class="fw-bold mb-1 text-success">🌿 {{ $order->gift_plant_name }}</h5>
          <p class="text-muted small mb-0">{{ __('messages.order_gift_plant_desc') }}</p>
        </div>
        <div class="col-md-3 text-md-end mt-3 mt-md-0">
          <div class="fw-bold text-success fs-5">$0 (GRATIS)</div>
          @if($order->gift_plant_detail_url)
            <a href="{{ $order->gift_plant_detail_url }}" target="_blank" class="btn btn-outline-success btn-sm mt-1">
              <i class="bi bi-box-arrow-up-right me-1"></i> Ver en tienda aliada
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Digital Blockchain Certificates Section --}}
  @php($certificates = $order->items->pluck('certificate')->filter())
  @if($certificates->isNotEmpty())
    <div class="mb-4">
      <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-patch-check-fill fs-3 text-warning"></i>
        <div>
          <h4 class="mb-0 fw-bold">{{ __('messages.cert_box_title') }}</h4>
          <small class="text-muted">{{ __('messages.cert_box_desc') }}</small>
        </div>
      </div>

      <div class="row g-3">
        @foreach($order->items as $item)
          @if($item->certificate)
            @php($cert = $item->certificate)
            <div class="col-md-6 col-lg-12">
              <div class="card card-soft p-3 border-start border-4 border-warning shadow-sm">
                <div class="row align-items-center">
                  <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <span class="badge {{ $cert->status === 'mined' ? 'bg-success' : 'bg-warning text-dark' }}">
                        <i class="bi {{ $cert->status === 'mined' ? 'bi-check-circle' : 'bi-hourglass-split' }}"></i>
                        {{ $cert->status === 'mined' ? __('messages.cert_mined') : __('messages.cert_pending') }}
                      </span>
                      @if($cert->block_index)
                        <span class="badge bg-dark">{{ __('messages.cert_block_index') }}{{ $cert->block_index }}</span>
                      @endif
                    </div>
                    <h5 class="fw-bold mb-1">{{ $item->watch->brand }} — {{ $item->watch->name }}</h5>
                    <p class="mb-1 text-muted small">
                      <strong>{{ __('messages.cert_serial_number') }}:</strong>
                      <span class="font-monospace text-primary fw-bold">{{ $cert->serial_number }}</span>
                    </p>
                    <p class="mb-0 text-muted small text-truncate">
                      <strong>{{ __('messages.cert_short_hash') }}:</strong>
                      <span class="font-monospace">{{ $cert->certificate_hash }}</span>
                    </p>
                  </div>
                  <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('certificates.show', $cert->certificate_hash) }}" class="btn btn-brand btn-sm px-3 py-2">
                      <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('messages.cert_view_blockchain') }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ route('watch.index') }}" class="btn btn-brand">{{ __('messages.order_continue_shopping') }}</a>
    <a href="{{ route('home.index') }}" class="btn btn-ghost">{{ __('messages.nav_home') }}</a>
  </div>
</div>
@endsection
