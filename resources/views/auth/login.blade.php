@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Iniciar sesión</div>
      <div class="card-body">
        <form method="POST" action="{{ route('login.store') }}">
          @csrf
          <input type="email" class="form-control mb-2" placeholder="Correo" name="email" value="{{ old('email') }}" required />
          <input type="password" class="form-control mb-2" placeholder="Contraseña" name="password" required />
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" />
            <label class="form-check-label" for="remember">Recordarme</label>
          </div>
          <button type="submit" class="btn btn-primary">Entrar</button>
          <a href="{{ route('register') }}" class="ms-2">Crear cuenta</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
