<div class="d-flex flex-wrap gap-2 align-items-center my-2 {{ ($showCurrency ?? true) ? 'my-xl-0 mx-xl-2' : 'my-lg-0 mx-lg-2' }}">
  <form method="POST" action="{{ route('preferences.update', 'locale') }}">
    @csrf
    <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('messages.language') }}">
      @foreach(['es' => 'ES', 'en' => 'EN'] as $locale => $label)
        <button type="submit" name="value" value="{{ $locale }}" class="btn {{ app()->getLocale() === $locale ? 'btn-secondary' : 'btn-outline-secondary' }}" aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}" lang="{{ $locale }}">{{ $label }}</button>
      @endforeach
    </div>
  </form>
  @if($showCurrency ?? true)
    <form method="POST" action="{{ route('preferences.update', 'currency') }}">
      @csrf
      <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('messages.currency') }}">
        @foreach(['COP', 'USD'] as $currency)
          <button type="submit" name="value" value="{{ $currency }}" class="btn {{ $prices->currency() === $currency ? 'btn-secondary' : 'btn-outline-secondary' }}" aria-pressed="{{ $prices->currency() === $currency ? 'true' : 'false' }}">{{ $currency }}</button>
        @endforeach
      </div>
    </form>
  @endif
</div>
