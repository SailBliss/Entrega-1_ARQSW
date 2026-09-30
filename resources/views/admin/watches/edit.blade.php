@extends('admin.layouts.admin')
@section('title', $title)
@section('heading', __('messages.admin_watches_edit'))
@section('content')
<form method="POST" action="{{ route('admin.watches.update', $watch) }}">
  @csrf @method('PUT')
  @include('admin.watches._form', ['submit' => __('messages.admin_watches_save')])
</form>
@endsection
