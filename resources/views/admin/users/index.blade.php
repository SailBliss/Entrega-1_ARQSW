@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', __('messages.admin_users_title'))
@section('content')
<div class="mb-3">
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
    + {{ __('messages.admin_users_btn_create') }}
  </a>
</div>

@if($users->isEmpty())
  <div class="alert alert-info text-center">
    {{ __('messages.admin_users_empty') }}
  </div>
@else
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col" style="width: 80px;">{{ __('messages.admin_users_col_id') }}</th>
            <th scope="col">{{ __('messages.admin_users_col_name') }}</th>
            <th scope="col">{{ __('messages.admin_users_col_email') }}</th>
            <th scope="col" style="width: 140px;">{{ __('messages.admin_users_col_role') }}</th>
            <th scope="col" style="width: 180px;">{{ __('messages.admin_users_col_registered_at') }}</th>
            <th scope="col" class="text-end" style="width: 180px;">{{ __('messages.admin_users_col_actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
            <tr>
              <td><span class="text-muted fw-bold">#{{ $user->id }}</span></td>
              <td class="fw-semibold">{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                @if($user->isAdmin())
                  <span class="badge bg-success">{{ __('messages.admin_users_role_admin') }}</span>
                @else
                  <span class="badge bg-secondary">{{ __('messages.admin_users_role_user') }}</span>
                @endif
              </td>
              <td><small class="text-muted">{{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</small></td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                    {{ __('messages.admin_users_btn_edit') }}
                  </a>
                  <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('messages.admin_users_confirm_delete') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      {{ __('messages.admin_users_btn_delete') }}
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3 d-flex justify-content-center">
    {{ $users->links() }}
  </div>
@endif
@endsection
