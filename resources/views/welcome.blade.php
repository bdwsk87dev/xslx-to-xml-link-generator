@extends('app')

@section('content')
    <h1>Welcome to the Application</h1>
    <p>Please login to access the site.</p>
    <a href="{{ route('login') }}">Login</a>
@endsection
