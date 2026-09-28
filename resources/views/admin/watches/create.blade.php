@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', 'Nuevo reloj')
@section('content')
<form method="POST" action="{{ route('admin.watches.store') }}">
  @csrf
  @include('admin.watches._form', ['submit' => 'Crear reloj'])
</form>
@endsection
