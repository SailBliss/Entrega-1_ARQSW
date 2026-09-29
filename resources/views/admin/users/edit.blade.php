@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', __('messages.admin_users_edit_subtitle', ['name' => $user->name]))
@section('content')
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">{{ __('messages.admin_users_edit_subtitle', ['name' => $user->name]) }}</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label for="name" class="form-label">{{ __('messages.admin_users_field_name') }}</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus />
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">{{ __('messages.admin_users_field_email') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required />
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">{{ __('messages.admin_users_field_password') }}</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" />
            <small class="form-text text-muted">{{ __('messages.admin_users_field_password_edit_help') }}</small>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('messages.admin_users_field_password_confirmation') }}</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" />
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->isAdmin()) ? 'checked' : '' }} />
            <label class="form-check-label" for="is_admin">
              {{ __('messages.admin_users_field_is_admin') }}
            </label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
              {{ __('messages.admin_users_btn_cancel') }}
            </a>
            <button type="submit" class="btn btn-primary">
              {{ __('messages.admin_users_btn_update') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
