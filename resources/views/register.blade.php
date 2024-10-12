@extends('layout.app')
@section('content')

<!-- Registration Form -->
<div class="container mt-5">
    <h2 id="register">Register</h2>
    <form>
        <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="registerEmail" class="form-label">Email:</label>
            <input type="email" class="form-control" id="registerEmail" name="email" required>
        </div>
        <div class="mb-3">
            <label for="registerPassword" class="form-label">Password:</label>
            <input type="password" class="form-control" id="registerPassword" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password:</label>
            <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required>
        </div>
        <button id="id-btn-register" class="btn btn-success">Register</button>
    </form>
</div>
@endsection