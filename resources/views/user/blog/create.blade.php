@extends('layouts.user')
@section('title', 'Create Blog Article')
@section('heading', 'Create Blog Article')
@section('content')
@include('user.blog.form', ['post' => null, 'action' => route('user.blog.store'), 'method' => 'POST'])
@endsection
