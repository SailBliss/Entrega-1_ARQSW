<div class="mb-3">
  <label for="name" class="form-label">{{ __('messages.admin_watches_field_name') }}</label>
  <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $watch->name) }}" required />
</div>
<div class="mb-3">
  <label for="brand" class="form-label">{{ __('messages.admin_watches_field_brand') }}</label>
  <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', $watch->brand) }}" required />
</div>
<div class="mb-3">
  <label for="description" class="form-label">{{ __('messages.admin_watches_field_description') }}</label>
  <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $watch->description) }}</textarea>
</div>
<div class="row">
  <div class="col-md-6 mb-3">
    <label for="price" class="form-label">{{ __('messages.admin_watches_field_price') }}</label>
    <input type="number" class="form-control" id="price" name="price" min="1" value="{{ old('price', $watch->price) }}" required />
  </div>
  <div class="col-md-6 mb-3">
    <label for="stock" class="form-label">{{ __('messages.admin_watches_field_stock') }}</label>
    <input type="number" class="form-control" id="stock" name="stock" min="0" value="{{ old('stock', $watch->stock ?? 0) }}" required />
  </div>
</div>
<div class="mb-3">
  <label for="image" class="form-label">{{ __('messages.admin_watches_field_image') }}</label>
  <input type="text" class="form-control" id="image" name="image" value="{{ old('image', $watch->image) }}" />
</div>
<button type="submit" class="btn btn-primary">{{ $submit }}</button>
<a href="{{ route('admin.watches.index') }}" class="btn btn-link">{{ __('messages.admin_users_btn_cancel') }}</a>
