<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
  <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />
  <title>@yield('title', 'Tienda Relojes')</title>
</head>
<body>
  
  <nav class="navbar navbar-expand-lg navbar-dark bg-secondary py-4">
    <div class="container">
      <a class="navbar-brand" href="{{ route('home.index') }}">Tienda Relojes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <form class="d-flex ms-lg-4 my-2 my-lg-0" method="GET" action="{{ route('watch.index') }}">
          <input class="form-control me-2" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar reloj..." aria-label="Buscar" />
          <button class="btn btn-outline-light" type="submit">Buscar</button>
        </form>
        <div class="navbar-nav ms-auto">
          <a class="nav-link active" href="{{ route('home.index') }}">Inicio</a>
          <a class="nav-link active" href="{{ route('watch.index') }}">Relojes</a>
          @auth
            <a class="nav-link active" href="{{ route('wishlist.index') }}">Deseados</a>
            <a class="nav-link active" href="{{ route('cart.index') }}">Carrito ({{ auth()->user()->cartItems()->sum('quantity') }})</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="nav-link active btn btn-link">Cerrar sesión ({{ auth()->user()->name }})</button>
            </form>
          @else
            <a class="nav-link active" href="{{ route('login') }}">Iniciar sesión</a>
            <a class="nav-link active" href="{{ route('register') }}">Registrarse</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>

  <header class="masthead bg-primary text-white text-center py-4">
    <div class="container d-flex align-items-center flex-column">
      <h2>@yield('subtitle', 'Relojes para cada momento')</h2>
    </div>
  </header>
  

  <div class="container my-4">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <ul class="alert alert-danger list-unstyled">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    @endif

    @yield('content')
  </div>

  
  <div class="copyright py-4 text-center text-white">
    <div class="container">
      <small>Tienda Relojes &copy; {{ date('Y') }}</small>
    </div>
  </div>
  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
