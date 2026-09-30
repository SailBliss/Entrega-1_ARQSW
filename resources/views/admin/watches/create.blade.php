@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', __('messages.admin_watches_new'))
@section('content')
<form method="POST" action="{{ route('admin.watches.store') }}">
  @csrf
  @include('admin.watches._form', ['submit' => __('messages.admin_watches_create')])
</form>
@endsection
