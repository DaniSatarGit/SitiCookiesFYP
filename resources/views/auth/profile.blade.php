@extends('layouts.site')

@section('title', 'User Profile - Siti Cookies Shop')

@section('content')
    <main class="container">
                <div class="card" style="max-width: 980px; margin: 0 auto;">


            <h1 class="center">User Profile</h1>

            @if (session('success'))
                <div class="flash success" role="alert">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash error" role="alert">{{ session('error') }}</div>
            @endif

            <section class="panel">
                <h2>Account</h2>
                <div class="grid" style="gap: 8px;">
                    <p><strong>Username:</strong> {{ session('username') }}</p>
                    <p><strong>Email:</strong> {{ $user->email ?? 'Not available' }}</p>
                </div>
            </section>

            <section class="panel" style="margin-top: 12px;">
                <h2>Change Password</h2>

                @if ($errors->any())
                    <div class="flash error" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="grid" method="POST" action="{{ route('profile.password') }}">
                    @csrf

                    <label for="old_password">Old Password:</label>
                    <input
                        type="password"
                        id="old_password"
                        name="old_password"
                        autocomplete="current-password"
                        required
                    >

                    <label style="display:flex; align-items:center; gap:10px;">
                        <input
                            type="checkbox"
                            style="width:auto;"
                            onclick="togglePassword()"
                        >
                        <span>Show Password</span>
                    </label>

                    <label for="new_password">New Password:</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        autocomplete="new-password"
                        required
                    >

                    <input type="submit" value="Change Password" class="auth-submit">
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>

        function togglePassword() {
            const oldPassword = document.getElementById('old_password');
            const newPassword = document.getElementById('new_password');
            const nextType = oldPassword.type === 'password' ? 'text' : 'password';
            oldPassword.type = nextType;
            newPassword.type = nextType;
        }
    </script>
@endpush

