<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showSignup(): View
    {
        return view('auth.signup');
    }

    public function legacyLoginSignup(): RedirectResponse
    {
        return redirect()->route('login');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'reenter_password' => ['required', 'same:password'],
        ]);

        $exists = DB::table('userdata')->where('username', $data['username'])->exists();

        if ($exists) {
            return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
        }

        DB::table('userdata')->insert([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $this->hashPassword($data['password']),
        ]);

        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login_username' => ['required', 'string'],
            'login_password' => ['required', 'string'],
        ]);

        $username = trim($data['login_username']);
        $password = $data['login_password'];

        if ($this->verifyAdminCredentials($username, $password)) {
            $this->loginUser($username, 'admin');

            return redirect()->route('admin.products');
        }

        $user = DB::table('userdata')
            ->select('username', 'password')
            ->where('username', $username)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            $this->loginUser($user->username, 'customer');

            return redirect()->route('home');
        }

        return redirect()->route('login')->with('error', 'Invalid username or password.');
    }

    public function logout(): RedirectResponse
    {
        session()->flush();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    public function profile(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $user = DB::table('userdata')
            ->select('email', 'password')
            ->where('username', $this->currentUser())
            ->first();

        return view('auth.profile', compact('user'));
    }

    public function changePassword(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $user = DB::table('userdata')
            ->select('password')
            ->where('username', $this->currentUser())
            ->first();

        if (! $user || ! Hash::check($data['old_password'], $user->password)) {
            return redirect()->route('profile')->with('error', 'Old password is incorrect.');
        }

        DB::table('userdata')
            ->where('username', $this->currentUser())
            ->update(['password' => $this->hashPassword($data['new_password'])]);

        return redirect()->route('profile')->with('success', 'Password changed successfully!');
    }

    public function forgotPassword(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $emailExists = DB::table('userdata')->where('email', $data['email'])->exists();

            if (! $emailExists) {
                return back()->withErrors(['email' => 'No account found with that email.'])->withInput();
            }

            $token = Str::random(100);
            $insert = [
                'email' => $data['email'],
                'token' => $token,
            ];

            if (Schema::hasColumn('password_resets', 'created_at')) {
                $insert['created_at'] = now();
            }

            DB::table('password_resets')->insert($insert);

            $resetLink = route('password.reset', ['token' => $token]);
            $sent = @mail($data['email'], 'Password Reset', "Click this link to reset your password: $resetLink", 'From: no-reply@siticookies.test');

            if (! $sent) {
                Log::info('Password reset link generated', ['email' => $data['email'], 'link' => $resetLink]);
            }

            return redirect()->route('login')->with('success', 'Password reset link has been sent to your email.');
        }

        return view('auth.forgot-password');
    }

    public function resetPassword(Request $request): View|RedirectResponse
    {
        $token = (string) $request->query('token', $request->input('token', ''));
        $reset = DB::table('password_resets')->where('token', $token)->first();

        if (! $reset) {
            abort(404, 'Invalid or expired token.');
        }

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'password' => ['required', 'string', 'min:6'],
                'reenter_password' => ['required', 'same:password'],
                'token' => ['required', 'string'],
            ]);

            DB::table('userdata')
                ->where('email', $reset->email)
                ->update(['password' => $this->hashPassword($data['password'])]);

            DB::table('password_resets')->where('token', $token)->delete();

            return redirect()->route('login')->with('success', 'Password reset successful. Please login.');
        }

        return view('auth.reset-password', compact('token'));
    }
}
