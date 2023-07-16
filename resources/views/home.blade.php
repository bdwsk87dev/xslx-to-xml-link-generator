@extends('app')

@section('content')
    <h1>Welcome to the Home Page</h1>
    <p>You are logged in.</p>
    <a href="{{ route('logout') }}">Logout</a>
@endsection
