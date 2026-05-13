@extends('layouts.site')

@section('title', 'Reset Password')

@section('content')
    <main class="container">
        <section class="card" style="max-width:480px;margin:0 auto;">
            <h2 class="center">Reset Password</h2>
            <form class="grid" action="{{ route('password.reset') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="password" name="password" placeholder="Enter new password" required>
                <div class="error-text">{{ $errors->first('password') }}</div>
                <input type="password" name="reenter_password" placeholder="Re-enter new password" required>
                <div class="error-text">{{ $errors->first('reenter_password') }}</div>
                <button type="submit">Submit</button>
            </form>
        </section>
    </main>
@endsection
