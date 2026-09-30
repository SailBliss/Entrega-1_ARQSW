@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="text-center mb-4">
        <i class="bi bi-watch" style="font-size: 2rem; color: var(--brand);"></i>
        <h3 class="mt-2 mb-1">Únete a la colección</h3>
        <p class="text-muted">Crea tu cuenta y guarda tus relojes favoritos.</p>
      </div>
      <div class="card card-soft">
        <div class="card-body">
          <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <input type="text" class="form-control mb-3" placeholder="Nombre" name="name" value="{{ old('name') }}" required />
            <input type="email" class="form-control mb-3" placeholder="Correo" name="email" value="{{ old('email') }}" required />
            <input type="password" class="form-control mb-3" placeholder="Contraseña (mín. 8 caracteres)" name="password" required />
            <input type="password" class="form-control mb-3" placeholder="Confirmar contraseña" name="password_confirmation" required />
            <button type="submit" class="btn btn-brand w-100 mb-2">Crear cuenta</button>
            <p class="text-center text-muted mb-0">¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
