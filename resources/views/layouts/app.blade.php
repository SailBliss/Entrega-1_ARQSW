{{-- Isabela Ruiz, Nicolas Ortiz, Miguel Angel Rendon --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="{{ asset('/css/app.css') }}" rel="stylesheet" />
  <title>@yield('title', __('messages.app_name'))</title>
</head>
<body>
  @inject('prices', 'App\Services\PriceFormatter')

  <nav class="navbar navbar-expand-xl site-navbar py-3">
    <div class="container">
      <a class="navbar-brand" href="{{ route('home.index') }}"><i class="bi bi-watch"></i> {{ __('messages.app_name') }}</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="{{ __('messages.toggle_navigation') }}">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <form class="search-pill ms-xl-3 my-3 my-xl-0" method="GET" action="{{ route('watch.index') }}">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search_placeholder') }}" aria-label="{{ __('messages.search_button') }}" />
          <button type="submit" aria-label="{{ __('messages.search_button') }}"><i class="bi bi-search"></i></button>
        </form>
        <div class="navbar-nav ms-auto align-items-xl-center gap-1">
          <a class="nav-link" href="{{ route('home.index') }}">{{ __('messages.nav_home') }}</a>
          <a class="nav-link" href="{{ route('watch.index') }}">{{ __('messages.nav_watches') }}</a>
          @include('partials.preferences')
          @auth
            <a class="icon-link" href="{{ route('wishlist.index') }}" title="{{ __('messages.nav_wishlist') }}"><i class="bi bi-heart"></i></a>
            <a class="icon-link" href="{{ route('cart.index') }}" title="{{ __('messages.cart_subtitle') }}">
              <i class="bi bi-bag"></i>
              @php($cartCount = auth()->user()->cartItems()->sum('quantity'))
              @if($cartCount > 0)
                <span class="icon-badge">{{ $cartCount }}</span>
              @endif
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline ms-xl-2">
              @csrf
              <button type="submit" class="btn btn-ghost">{{ __('messages.nav_logout_greeting', ['name' => explode(' ', auth()->user()->name)[0]]) }}</button>
            </form>
          @else
            <a class="btn btn-ghost px-xl-3 text-nowrap" href="{{ route('login') }}">{{ __('messages.nav_login') }}</a>
            <a class="btn btn-brand px-xl-3 text-nowrap ms-xl-2" href="{{ route('register') }}">{{ __('messages.auth_btn_register') }}</a>
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
      <p class="mb-1"><i class="bi bi-watch"></i> {{ __('messages.app_name') }}</p>
      <small>{{ __('messages.footer_tagline', ['year' => date('Y')]) }}</small>
      @if($prices->notice() === 'api')
        <p class="small mt-2 mb-0">{{ __('messages.currency_visual_only') }} <a href="https://www.exchangerate-api.com" lang="en">Rates By Exchange Rate API</a></p>
      @elseif($prices->notice() === 'demo')
        <p class="small mt-2 mb-0">{{ __('messages.currency_demo') }} {{ __('messages.currency_visual_only') }}</p>
      @elseif($prices->notice() === 'unavailable')
        <p class="small mt-2 mb-0" role="status">{{ __('messages.currency_unavailable') }}</p>
      @endif
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
