@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">{{ __('messages.auth_register_card_header') }}</div>
      <div class="card-body">
        <form method="POST" action="{{ route('register.store') }}">
          @csrf
          <input type="text" class="form-control mb-2" placeholder="{{ __('messages.auth_name_placeholder') }}" name="name" value="{{ old('name') }}" required />
          <input type="email" class="form-control mb-2" placeholder="{{ __('messages.auth_email_placeholder') }}" name="email" value="{{ old('email') }}" required />
          <input type="password" class="form-control mb-2" placeholder="{{ __('messages.auth_password_placeholder') }}" name="password" required />
          <input type="password" class="form-control mb-2" placeholder="{{ __('messages.auth_password_confirm_placeholder') }}" name="password_confirmation" required />
          <button type="submit" class="btn btn-primary">{{ __('messages.auth_btn_register') }}</button>
          <a href="{{ route('login') }}" class="ms-2">{{ __('messages.auth_has_account') }}</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
