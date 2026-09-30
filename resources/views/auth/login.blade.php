@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="text-center mb-4">
        <i class="bi bi-watch" style="font-size: 2rem; color: var(--brand);"></i>
        <h3 class="mt-2 mb-1">Bienvenido de nuevo</h3>
        <p class="text-muted">Nos alegra verte otra vez por aquí.</p>
      </div>
      <div class="card card-soft">
        <div class="card-body">
          <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <input type="email" class="form-control mb-3" placeholder="Correo" name="email" value="{{ old('email') }}" required />
            <input type="password" class="form-control mb-3" placeholder="Contraseña" name="password" required />
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="remember" id="remember" />
              <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            <button type="submit" class="btn btn-brand w-100 mb-2">Entrar</button>
            <p class="text-center text-muted mb-0">¿Aún no tienes cuenta? <a href="{{ route('register') }}">Crear cuenta</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
