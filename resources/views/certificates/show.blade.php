{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      
      {{-- Status Banner --}}
      @if($consensus['consensus'])
        <div class="card card-soft border-start border-4 border-success p-3 mb-4 shadow-sm">
          <div class="d-flex align-items-center">
            <i class="bi bi-shield-fill-check fs-1 text-success me-3"></i>
            <div>
              <h4 class="mb-1 fw-bold text-success">{{ __('messages.cert_status_verified') }}</h4>
              <p class="mb-0 text-muted">{{ __('messages.cert_consensus_achieved') }} ({{ __('messages.cert_nodes_validated', ['verified' => $consensus['verified_nodes_count'], 'total' => $consensus['total_nodes']]) }})</p>
            </div>
          </div>
        </div>
      @else
        <div class="card card-soft border-start border-4 border-warning p-3 mb-4 shadow-sm">
          <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-1 text-warning me-3"></i>
            <div>
              <h4 class="mb-1 fw-bold text-warning">{{ __('messages.cert_status_unverified') }}</h4>
              <p class="mb-0 text-muted">{{ __('messages.cert_consensus_failed') }} ({{ __('messages.cert_nodes_reported', ['verified' => $consensus['verified_nodes_count'], 'total' => $consensus['total_nodes']]) }})</p>
            </div>
          </div>
        </div>
      @endif

      {{-- Certificate Card --}}
      <div class="card card-soft mb-4 shadow-sm">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4">
            <div>
              <span class="badge bg-dark mb-1"><i class="bi bi-link-45deg"></i> {{ __('messages.cert_blockchain_verified') }}</span>
              <h3 class="fw-bold mb-0">📜 {{ __('messages.cert_box_title') }}</h3>
            </div>
            @if($certificate)
              <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">{{ __('messages.cert_order_number', ['id' => $certificate->order_id]) }}</span>
            @endif
          </div>

          <div class="row align-items-center">
            @php($watchImg = $certificate?->watch?->image ?? 'img/watches/generic.svg')
            <div class="col-md-3 text-center mb-3 mb-md-0">
              <div class="p-3 bg-light rounded-3 border">
                <img src="{{ asset($watchImg) }}" class="img-fluid" style="max-height: 160px; object-fit: contain;" alt="{{ $certificate?->watch?->name ?? __('messages.cert_watch_name') }}">
              </div>
            </div>
            <div class="col-md-9">
              <table class="table table-borderless mb-0">
                <tbody>
                  <tr>
                    <th class="text-muted py-2" style="width: 32%;">{{ __('messages.cert_serial_number') }}</th>
                    <td class="py-2"><span class="font-monospace fs-5 fw-bold text-primary">{{ $certificate->serial_number ?? ($consensus['nodes']['store']['data']['payload']['serial_number'] ?? __('messages.cert_not_available')) }}</span></td>
                  </tr>
                  <tr>
                    <th class="text-muted py-2">{{ __('messages.cert_watch_name') }}</th>
                    <td class="py-2">
                      <strong class="fs-6">{{ $certificate->watch->brand ?? ($consensus['nodes']['store']['data']['payload']['watch_brand'] ?? '') }}</strong> 
                      {{ $certificate->watch->name ?? ($consensus['nodes']['store']['data']['payload']['watch_name'] ?? __('messages.cert_not_available')) }}
                    </td>
                  </tr>
                  <tr>
                    <th class="text-muted py-2">{{ __('messages.cert_buyer') }}</th>
                    <td class="py-2">{{ $certificate->user->name ?? ($consensus['nodes']['store']['data']['payload']['buyer_name'] ?? __('messages.cert_not_available')) }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted py-2">{{ __('messages.cert_issue_date') }}</th>
                    <td class="py-2">{{ $certificate->created_at?->format('d/m/Y H:i:s') ?? ($consensus['nodes']['store']['data']['payload']['issued_at'] ?? __('messages.cert_not_available')) }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted py-2">{{ __('messages.cert_block_index') }}</th>
                    <td class="py-2"><span class="badge bg-dark px-2 py-1">{{ __('messages.cert_block_index') }}{{ $certificate->block_index ?? ($consensus['nodes']['store']['data']['block_index'] ?? __('messages.cert_not_available')) }}</span></td>
                  </tr>
                  <tr>
                    <th class="text-muted py-2">{{ __('messages.cert_block_hash') }}</th>
                    <td class="py-2"><small class="text-break font-monospace text-secondary">{{ $consensus['consistent_block_hash'] ?? ($certificate->block_hash ?? __('messages.cert_not_available')) }}</small></td>
                  </tr>
                  <tr>
                    <th class="text-muted py-2">{{ __('messages.cert_hash') }}</th>
                    <td class="py-2"><small class="text-break font-monospace text-success fw-bold">{{ $hash }}</small></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- P2P Network Nodes Consensus Table --}}
      <div class="card card-soft mb-4 shadow-sm">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-hdd-network-fill fs-4 text-primary"></i>
            <h5 class="mb-0 fw-bold">{{ __('messages.cert_network_nodes') }}</h5>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr class="text-muted small">
                  <th>{{ __('messages.cert_node_name') }}</th>
                  <th>{{ __('messages.cert_node_endpoint') }}</th>
                  <th>{{ __('messages.cert_node_status') }}</th>
                  <th>{{ __('messages.cert_node_verdict') }}</th>
                  <th>{{ __('messages.cert_block_index') }}</th>
                  <th>{{ __('messages.cert_node_block_hash') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($consensus['nodes'] as $name => $node)
                  <tr>
                    <td><strong>{{ ucfirst($name) }}</strong></td>
                    <td><code>{{ $node['node_url'] }}</code></td>
                    <td>
                      @if($node['online'])
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-wifi"></i> {{ __('messages.cert_node_online') }}</span>
                      @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-wifi-off"></i> {{ __('messages.cert_node_offline') }}</span>
                      @endif
                    </td>
                    <td>
                      @if(!empty($node['data']['verified']))
                        <span class="badge bg-success"><i class="bi bi-check-lg"></i> {{ __('messages.cert_node_authentic') }}</span>
                      @else
                        <span class="badge bg-danger"><i class="bi bi-x-lg"></i> {{ __('messages.cert_node_unverified') }}</span>
                      @endif
                    </td>
                    <td>{{ $node['data']['block_index'] ?? '-' }}</td>
                    <td><span class="font-monospace text-muted">{{ isset($node['data']['block_hash']) ? substr($node['data']['block_hash'], 0, 16).'...' : '-' }}</span></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('watch.index') }}" class="btn btn-ghost">« {{ __('messages.back_to_catalog') }}</a>
        <button class="btn btn-brand" onclick="window.print()"><i class="bi bi-printer me-1"></i> {{ __('messages.cert_print') }}</button>
      </div>

    </div>
  </div>
</div>
@endsection
