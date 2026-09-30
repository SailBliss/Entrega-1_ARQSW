<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />
  <title>@yield('title', 'Tienda Relojes')</title>
</head>
<body>

  <nav class="navbar navbar-expand-lg site-navbar py-3">
    <div class="container">
      <a class="navbar-brand" href="{{ route('home.index') }}"><i class="bi bi-watch"></i> Tienda Relojes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <form class="search-pill ms-lg-4 my-3 my-lg-0" method="GET" action="{{ route('watch.index') }}">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="Busca tu próximo reloj..." aria-label="Buscar" />
          <button type="submit" aria-label="Buscar"><i class="bi bi-search"></i></button>
        </form>
        <div class="navbar-nav ms-auto align-items-lg-center gap-1">
          <a class="nav-link" href="{{ route('home.index') }}">Inicio</a>
          <a class="nav-link" href="{{ route('watch.index') }}">Relojes</a>
          @auth
            <a class="icon-link" href="{{ route('wishlist.index') }}" title="Deseados"><i class="bi bi-heart"></i></a>
            <a class="icon-link" href="{{ route('cart.index') }}" title="Carrito">
              <i class="bi bi-bag"></i>
              @php($cartCount = auth()->user()->cartItems()->sum('quantity'))
              @if($cartCount > 0)
                <span class="icon-badge">{{ $cartCount }}</span>
              @endif
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline ms-lg-2">
              @csrf
              <button type="submit" class="btn btn-ghost">Hola, {{ explode(' ', auth()->user()->name)[0] }} · Salir</button>
            </form>
          @else
            <a class="btn btn-ghost" href="{{ route('login') }}">Iniciar sesión</a>
            <a class="btn btn-brand ms-lg-2" href="{{ route('register') }}">Crear cuenta</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <ul class="alert alert-danger list-unstyled mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    @endif
  </div>

  @yield('content')

  <footer class="site-footer py-5 mt-5">
    <div class="container text-center">
      <p class="mb-1"><i class="bi bi-watch"></i> Tienda Relojes</p>
      <small>Hecho con cariño para quienes coleccionan momentos. &copy; {{ date('Y') }}</small>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
