@extends('layouts.app')
@section('title', $title)
@section('subtitle', $subtitle)
@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Registrarse</div>
      <div class="card-body">
        <form method="POST" action="{{ route('register.store') }}">
          @csrf
          <input type="text" class="form-control mb-2" placeholder="Nombre" name="name" value="{{ old('name') }}" required />
          <input type="email" class="form-control mb-2" placeholder="Correo" name="email" value="{{ old('email') }}" required />
          <input type="password" class="form-control mb-2" placeholder="Contraseña (mín. 8 caracteres)" name="password" required />
          <input type="password" class="form-control mb-2" placeholder="Confirmar contraseña" name="password_confirmation" required />
          <button type="submit" class="btn btn-primary">Crear cuenta</button>
          <a href="{{ route('login') }}" class="ms-2">Ya tengo cuenta</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
