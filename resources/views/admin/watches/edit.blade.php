@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', 'Editar reloj')
@section('content')
<form method="POST" action="{{ route('admin.watches.update', $watch) }}">
  @csrf @method('PUT')
  @include('admin.watches._form', ['submit' => 'Guardar cambios'])
</form>
@endsection
