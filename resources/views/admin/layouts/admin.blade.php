<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
  <title>@yield('title', __('messages.admin_panel'))</title>
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="{{ route('admin.dashboard') }}">{{ __('messages.admin_panel') }}</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"
        aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="adminNav">
        <div class="navbar-nav me-auto">
          <a class="nav-link" href="{{ route('admin.watches.index') }}">{{ __('messages.nav_watches') }}</a>
          <a class="nav-link" href="{{ route('admin.watches.create') }}">{{ __('messages.admin_watches_new') }}</a>
          <a class="nav-link" href="{{ route('admin.users.index') }}">{{ __('messages.admin_users_nav') }}</a>
          <a class="nav-link" href="{{ route('admin.users.create') }}">{{ __('messages.admin_users_new') }}</a>
          <a class="nav-link" href="{{ route('home.index') }}">{{ __('messages.admin_view_store') }}</a>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="d-flex">
          @csrf
          <button type="submit" class="btn btn-outline-light btn-sm">{{ __('messages.admin_logout', ['name' => auth()->user()->name]) }}</button>
        </form>
      </div>
    </div>
  </nav>

  <main class="container my-4">
    <h1 class="h3 mb-4">@yield('heading')</h1>

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
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
