@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="text-center mb-4">
        <i class="bi bi-watch" style="font-size: 2rem; color: var(--brand);"></i>
        <h3 class="mt-2 mb-1">{{ __('messages.auth_join_collection') }}</h3>
        <p class="text-muted">{{ __('messages.auth_join_collection_text') }}</p>
      </div>
      <div class="card card-soft">
        <div class="card-body">
          <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <input type="text" class="form-control mb-3" placeholder="{{ __('messages.auth_name_placeholder') }}" name="name" value="{{ old('name') }}" required />
            <input type="email" class="form-control mb-3" placeholder="{{ __('messages.auth_email_placeholder') }}" name="email" value="{{ old('email') }}" required />
            <input type="password" class="form-control mb-3" placeholder="{{ __('messages.auth_password_placeholder') }}" name="password" required />
            <input type="password" class="form-control mb-3" placeholder="{{ __('messages.auth_password_confirm_placeholder') }}" name="password_confirmation" required />
            <button type="submit" class="btn btn-brand w-100 mb-2">{{ __('messages.auth_btn_register') }}</button>
            <p class="text-center text-muted mb-0">{{ __('messages.auth_has_account_question') }} <a href="{{ route('login') }}">{{ __('messages.nav_login') }}</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
