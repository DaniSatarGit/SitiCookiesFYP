@extends('layouts.site')

@section('title', 'Siti Cookies - Login')

@section('content')
    <main class="container auth-page">
        <section class="auth-shell">
            <div class="auth-visual">
                <div class="auth-brand">
                    <img src="{{ asset('assets/images/Logo.png') }}" alt="Siti Cookies">
                    <p>Fresh cookies, simple ordering.</p>
                </div>
            </div>

            <div class="auth-panel">
                <div class="auth-panel-header">
                    <span class="auth-eyebrow">Account</span>
                    <h1>Welcome Back</h1>
                    <p class="muted">Login to continue shopping at Siti Cookies.</p>
                </div>

                <form class="auth-form" action="{{ route('login.submit') }}" method="POST">
                    @csrf
                    <label>
                        Username
                        <input type="text" name="login_username" placeholder="Enter username" value="{{ old('login_username') }}" required>
                    </label>
                    <label>
                        Password
                        <input type="password" name="login_password" placeholder="Enter password" required>
                    </label>
                    <button type="submit" class="auth-submit">Sign In</button>
                </form>

                <div class="auth-links">
                    <a href="{{ route('password.forgot') }}">Forgot password?</a>
                    <span>New customer?</span>
                    <a href="{{ route('signup') }}">Create account</a>
                </div>
            </div>
        </section>
    </main>
@endsection
