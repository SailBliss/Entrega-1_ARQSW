{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
@extends('layouts.app')
@section('title', $title)
@section('content')
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
                      <i class="bi bi-shield-check"></i> Certificado #{{ substr($item->certificate->certificate_hash, 0, 8) }}...
                    </span>
                  </div>
                @endif
              </td>
              <td>${{ number_format($item->price, 0, ',', '.') }}</td>
              <td>{{ $item->quantity }}</td>
              <td>${{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">{{ __('messages.cart_col_total') }}</th>
            <th>${{ number_format($order->total, 0, ',', '.') }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

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
                        {{ $cert->status === 'mined' ? 'Minado en Blockchain' : 'Pendiente de Minado' }}
                      </span>
                      @if($cert->block_index)
                        <span class="badge bg-dark">Bloque #{{ $cert->block_index }}</span>
                      @endif
                    </div>
                    <h5 class="fw-bold mb-1">{{ $item->watch->brand }} — {{ $item->watch->name }}</h5>
                    <p class="mb-1 text-muted small">
                      <strong>{{ __('messages.cert_serial_number') }}:</strong>
                      <span class="font-monospace text-primary fw-bold">{{ $cert->serial_number }}</span>
                    </p>
                    <p class="mb-0 text-muted small text-truncate">
                      <strong>Hash SHA-256:</strong>
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
