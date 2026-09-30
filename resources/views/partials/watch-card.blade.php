<div class="col-md-6 col-lg-3 mb-4">
  <div class="card card-watch">
    <a href="{{ route('watch.show', $watch) }}" class="watch-img-wrap text-decoration-none">
      <img src="{{ asset($watch->image ?? 'img/watches/generic.svg') }}" class="watch-img" alt="{{ $watch->name }}">
    </a>
    <div class="card-body">
      <span class="brand-pill">{{ $watch->brand }}</span>
      <h5 class="card-title mt-2 mb-1"><a href="{{ route('watch.show', $watch) }}" class="text-decoration-none text-reset">{{ $watch->name }}</a></h5>
      <p class="watch-price mb-2">${{ number_format($watch->price, 0, ',', '.') }}</p>
      <p class="mb-3">
        @if($watch->stock > 0)
          <span class="stock-badge" style="color: var(--accent)"><span class="stock-dot in"></span>En stock</span>
        @else
          <span class="stock-badge" style="color: var(--danger)"><span class="stock-dot out"></span>Agotado</span>
        @endif
      </p>
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('watch.show', $watch) }}" class="btn btn-brand flex-grow-1">Ver detalle</a>
        @auth
          @if(in_array($watch->id, $wishlistIds ?? []))
            <form method="POST" action="{{ route('wishlist.remove', $watch) }}">
              @csrf @method('DELETE')
              <button class="btn-heart is-active" type="submit" title="Quitar de deseados"><i class="bi bi-heart-fill"></i></button>
            </form>
          @else
            <form method="POST" action="{{ route('wishlist.add', $watch) }}">
              @csrf
              <button class="btn-heart" type="submit" title="Añadir a deseados"><i class="bi bi-heart"></i></button>
            </form>
          @endif
        @endauth
      </div>
    </div>
  </div>
</div>
