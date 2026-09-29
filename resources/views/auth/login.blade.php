@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">{{ __('messages.auth_login_card_header') }}</div>
      <div class="card-body">
        <form method="POST" action="{{ route('login.store') }}">
          @csrf
          <input type="email" class="form-control mb-2" placeholder="{{ __('messages.auth_email_placeholder') }}" name="email" value="{{ old('email') }}" required />
          <input type="password" class="form-control mb-2" placeholder="{{ __('messages.auth_login_password_placeholder') }}" name="password" required />
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" />
            <label class="form-check-label" for="remember">{{ __('messages.auth_remember_me') }}</label>
          </div>
          <button type="submit" class="btn btn-primary">{{ __('messages.auth_btn_login') }}</button>
          <a href="{{ route('register') }}" class="ms-2">{{ __('messages.auth_no_account') }}</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
