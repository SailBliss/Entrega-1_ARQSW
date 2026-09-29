<div class="col-md-6 col-lg-3 mb-4">
  <div class="card h-100">
    <img src="{{ asset('img/watch.svg') }}" class="card-img-top watch-img" alt="{{ $watch->name }}">
    <div class="card-body text-center d-flex flex-column">
      <small class="text-muted">{{ $watch->brand }}</small>
      <h5 class="card-title">{{ $watch->name }}</h5>
      <p class="card-text fw-bold">${{ number_format($watch->price, 0, ',', '.') }}</p>
      <div class="mt-auto">
        <a href="{{ route('watch.show', $watch) }}" class="btn bg-primary text-white mb-2">{{ __('messages.view_detail') }}</a>
        @auth
          @if(in_array($watch->id, $wishlistIds ?? []))
            <form method="POST" action="{{ route('wishlist.remove', $watch) }}" class="d-inline">
              @csrf @method('DELETE')
              <button class="btn btn-outline-danger mb-2" type="submit">{{ __('messages.remove_from_wishlist') }}</button>
            </form>
          @else
            <form method="POST" action="{{ route('wishlist.add', $watch) }}" class="d-inline">
              @csrf
              <button class="btn btn-outline-secondary mb-2" type="submit">{{ __('messages.add_to_wishlist') }}</button>
            </form>
          @endif
        @endauth
      </div>
    </div>
  </div>
</div>
