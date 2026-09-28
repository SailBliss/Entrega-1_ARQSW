@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', 'Relojes')
@section('content')
<div class="mb-3">
  <a href="{{ route('admin.watches.create') }}" class="btn btn-primary">Nuevo reloj</a>
</div>

@if($watches->isEmpty())
  <p class="lead">No hay relojes registrados.</p>
@else
  <div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Precio</th><th>Stock</th><th class="text-end">Acciones</th></tr>
      </thead>
      <tbody>
        @foreach($watches as $watch)
          <tr>
            <td>{{ $watch->id }}</td>
            <td>{{ $watch->name }}</td>
            <td>{{ $watch->brand }}</td>
            <td>${{ number_format($watch->price, 0, ',', '.') }}</td>
            <td>{{ $watch->stock }}</td>
            <td class="text-end">
              <a href="{{ route('admin.watches.edit', $watch) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
              <form method="POST" action="{{ route('admin.watches.destroy', $watch) }}" class="d-inline"
                onsubmit="return confirm('¿Eliminar este reloj?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
@endsection
