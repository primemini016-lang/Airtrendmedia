@extends('layouts.user')
@section('title', 'Edit Blog Article')
@section('heading', 'Edit Blog Article')
@section('content')
@include('user.blog.form', ['post' => $post, 'action' => route('user.blog.update',$post), 'method' => 'POST'])
@endsection
