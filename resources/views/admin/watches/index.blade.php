{{-- Nicolas Ortiz --}}
@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', __('messages.nav_watches'))
@section('content')
<div class="mb-3">
  <a href="{{ route('admin.watches.create') }}" class="btn btn-primary">{{ __('messages.admin_watches_new') }}</a>
</div>

@if($watches->isEmpty())
  <p class="lead">{{ __('messages.admin_watches_empty') }}</p>
@else
  <div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
      <thead>
        <tr><th>ID</th><th>{{ __('messages.admin_watches_field_name') }}</th><th>{{ __('messages.admin_watches_field_brand') }}</th><th>{{ __('messages.admin_watches_field_price') }}</th><th>{{ __('messages.admin_watches_field_stock') }}</th><th class="text-end">{{ __('messages.admin_watches_actions') }}</th></tr>
      </thead>
      <tbody>
        @foreach($watches as $watch)
          <tr>
            <td>{{ $watch->id }}</td>
            <td>{{ $watch->name }}</td>
            <td>{{ $watch->brand }}</td>
            <td>${{ number_format($watch->price, 0, ',', '.') }}</td>
            <td>{{ $watch->stock }}</td>
            <td class="text-end">
              <a href="{{ route('admin.watches.edit', $watch) }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.admin_users_btn_edit') }}</a>
              <form method="POST" action="{{ route('admin.watches.destroy', $watch) }}" class="d-inline"
                onsubmit="return confirm('{{ __('messages.admin_watches_confirm_delete') }}');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('messages.admin_users_btn_delete') }}</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
@endsection
