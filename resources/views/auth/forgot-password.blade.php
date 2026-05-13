@extends('layouts.site')

@section('title', 'Forgot Password')

@section('content')
    <main class="container">
        <section class="card" style="max-width:480px;margin:0 auto;">
            <h2 class="center">Forgot Password</h2>
            <form class="grid" action="{{ route('password.forgot') }}" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                <div class="error-text">{{ $errors->first('email') }}</div>
                <button type="submit">Submit</button>
            </form>
        </section>
    </main>
@endsection
