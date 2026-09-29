@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">{{ __('messages.admin_users_create_subtitle') }}</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
          @csrf

          <div class="mb-3">
            <label for="name" class="form-label">{{ __('messages.admin_users_field_name') }}</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus />
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">{{ __('messages.admin_users_field_email') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required />
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">{{ __('messages.admin_users_field_password') }}</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required />
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('messages.admin_users_field_password_confirmation') }}</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required />
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }} />
            <label class="form-check-label" for="is_admin">
              {{ __('messages.admin_users_field_is_admin') }}
            </label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
              {{ __('messages.admin_users_btn_cancel') }}
            </a>
            <button type="submit" class="btn btn-primary">
              {{ __('messages.admin_users_btn_save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
