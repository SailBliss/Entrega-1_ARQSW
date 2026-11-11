@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">
    
    {{-- Status Banner --}}
    @if($consensus['consensus'])
      <div class="alert alert-success d-flex align-items-center mb-4 p-3 shadow-sm">
        <div class="fs-1 me-3">🛡️</div>
        <div>
          <h4 class="alert-heading mb-1">{{ __('messages.cert_status_verified') }}</h4>
          <p class="mb-0">{{ __('messages.cert_consensus_achieved') }} ({{ $consensus['verified_nodes_count'] }}/{{ $consensus['total_nodes'] }} nodos)</p>
        </div>
      </div>
    @else
      <div class="alert alert-warning d-flex align-items-center mb-4 p-3 shadow-sm">
        <div class="fs-1 me-3">⚠️</div>
        <div>
          <h4 class="alert-heading mb-1">{{ __('messages.cert_status_unverified') }}</h4>
          <p class="mb-0">{{ __('messages.cert_consensus_failed') }} ({{ $consensus['verified_nodes_count'] }}/{{ $consensus['total_nodes'] }} nodos validaron)</p>
        </div>
      </div>
    @endif

    {{-- Certificate Card --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">📜 Certificado Digital de Autenticidad</h5>
        @if($certificate)
          <span class="badge bg-info text-dark">ID DB: #{{ $certificate->id }}</span>
        @endif
      </div>
      <div class="card-body">
        <div class="row">
          @if($certificate && $certificate->watch && $certificate->watch->image)
            <div class="col-md-3 text-center mb-3 mb-md-0">
              <img src="{{ asset('/img/' . $certificate->watch->image) }}" class="img-fluid rounded border" alt="{{ $certificate->watch->name }}">
            </div>
            <div class="col-md-9">
          @else
            <div class="col-12">
          @endif
            <table class="table table-bordered table-striped mb-0">
              <tbody>
                <tr>
                  <th style="width: 30%;">{{ __('messages.cert_serial_number') }}</th>
                  <td><code class="fs-6 fw-bold text-primary">{{ $certificate->serial_number ?? ($consensus['nodes']['store']['data']['payload']['serial_number'] ?? 'N/A') }}</code></td>
                </tr>
                <tr>
                  <th>{{ __('messages.cert_watch_name') }}</th>
                  <td>
                    <strong>{{ $certificate->watch->brand ?? ($consensus['nodes']['store']['data']['payload']['watch_brand'] ?? '') }}</strong> 
                    {{ $certificate->watch->name ?? ($consensus['nodes']['store']['data']['payload']['watch_name'] ?? 'N/A') }}
                  </td>
                </tr>
                <tr>
                  <th>{{ __('messages.cert_buyer') }}</th>
                  <td>{{ $certificate->user->name ?? ($consensus['nodes']['store']['data']['payload']['buyer_name'] ?? 'N/A') }}</td>
                </tr>
                <tr>
                  <th>{{ __('messages.cert_issue_date') }}</th>
                  <td>{{ $certificate->created_at?->format('d/m/Y H:i:s') ?? ($consensus['nodes']['store']['data']['payload']['issued_at'] ?? 'N/A') }}</td>
                </tr>
                <tr>
                  <th>{{ __('messages.cert_block_index') }}</th>
                  <td><span class="badge bg-secondary">Bloque #{{ $certificate->block_index ?? ($consensus['nodes']['store']['data']['block_index'] ?? 'N/A') }}</span></td>
                </tr>
                <tr>
                  <th>{{ __('messages.cert_block_hash') }}</th>
                  <td><small class="text-break font-monospace">{{ $consensus['consistent_block_hash'] ?? ($certificate->block_hash ?? 'N/A') }}</small></td>
                </tr>
                <tr>
                  <th>{{ __('messages.cert_hash') }}</th>
                  <td><small class="text-break font-monospace text-success fw-bold">{{ $hash }}</small></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- P2P Network Nodes Consensus Table --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light py-3">
        <h5 class="mb-0">🌐 {{ __('messages.cert_network_nodes') }}</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>{{ __('messages.cert_node_name') }}</th>
                <th>Endpoint</th>
                <th>{{ __('messages.cert_node_status') }}</th>
                <th>{{ __('messages.cert_node_verdict') }}</th>
                <th>Bloque #</th>
                <th>Hash Bloque (primeros 16 caracteres)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($consensus['nodes'] as $name => $node)
                <tr>
                  <td><strong>{{ ucfirst($name) }}</strong></td>
                  <td><code>{{ $node['node_url'] }}</code></td>
                  <td>
                    @if($node['online'])
                      <span class="badge bg-success">Online</span>
                    @else
                      <span class="badge bg-danger">Offline</span>
                    @endif
                  </td>
                  <td>
                    @if(!empty($node['data']['verified']))
                      <span class="badge bg-success">✓ Auténtico</span>
                    @else
                      <span class="badge bg-danger">✗ No Validado</span>
                    @endif
                  </td>
                  <td>{{ $node['data']['block_index'] ?? '-' }}</td>
                  <td><span class="font-monospace">{{ isset($node['data']['block_hash']) ? substr($node['data']['block_hash'], 0, 16).'...' : '-' }}</span></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between">
      <a href="{{ route('watch.index') }}" class="btn btn-outline-secondary">« {{ __('messages.back_to_catalog') }}</a>
      <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir Certificado</button>
    </div>

  </div>
</div>
@endsection
