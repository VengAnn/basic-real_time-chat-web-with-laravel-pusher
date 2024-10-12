@extends('layout.app')
@section('content')
<div class="container mt-5">
    <!-- Login Form -->
    <h2>Login</h2>
    <form>
        @csrf
        <!-- Add this for CSRF protection in Laravel -->
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button id="id-btn-login" class="btn btn-primary">Login</button>
        <p class="mt-3">Don't have an account? <a href="/register">Register here</a></p>
    </form>

</div>
@endsection