@extends('layouts.site')

@section('title', 'Siti Cookies - Sign Up')

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
                    <span class="auth-eyebrow">Join Us</span>
                    <h1>Create Account</h1>
                    <p class="muted">Sign up and start building your cookie cart.</p>
                </div>

                <form class="auth-form" action="{{ route('signup.submit') }}" method="POST">
                    @csrf
                    <label>
                        Username
                        <input type="text" name="username" placeholder="Choose username" value="{{ old('username') }}" required>
                    </label>
                    <div class="error-text">{{ $errors->first('username') }}</div>

                    <label>
                        Email
                        <input type="email" name="email" placeholder="Enter email" value="{{ old('email') }}" required>
                    </label>
                    <div class="error-text">{{ $errors->first('email') }}</div>

                    <label>
                        Password
                        <input type="password" name="password" placeholder="Minimum 6 characters" required>
                    </label>
                    <div class="error-text">{{ $errors->first('password') }}</div>

                    <label>
                        Confirm Password
                        <input type="password" name="reenter_password" placeholder="Re-enter password" required>
                    </label>
                    <div class="error-text">{{ $errors->first('reenter_password') }}</div>

                    <button type="submit" class="auth-submit">Create Account</button>
                </form>

                <p class="auth-note">By creating an account, you agree to our Terms of Service and Privacy Policy.</p>
                <div class="auth-links">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </section>
    </main>
@endsection
