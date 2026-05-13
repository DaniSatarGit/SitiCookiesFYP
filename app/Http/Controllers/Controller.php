<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

abstract class Controller
{
    protected function currentUser(): ?string
    {
        return session('username');
    }

    protected function isLoggedIn(): bool
    {
        return session()->has('username');
    }

    protected function isAdmin(): bool
    {
        return session('role') === 'admin';
    }

    protected function requireLogin(): ?RedirectResponse
    {
        if ($this->isLoggedIn()) {
            return null;
        }

        return redirect()->route('login')->with('error', 'Please log in to continue.');
    }

    protected function requireAdmin(): ?RedirectResponse
    {
        if ($this->isAdmin()) {
            return null;
        }

        return redirect()->route('login')->with('error', 'Admin access is required.');
    }

    protected function verifyAdminCredentials(string $username, string $password): bool
    {
        $adminUsername = env('SITI_ADMIN_USERNAME', 'SitiAdmin');
        $adminPassword = env('SITI_ADMIN_PASSWORD', 'sitiadmin123');

        return hash_equals($adminUsername, $username)
            && hash_equals($adminPassword, $password);
    }

    protected function loginUser(string $username, string $role = 'customer'): void
    {
        session()->regenerate();
        session(['username' => $username, 'role' => $role]);
    }

    protected function hashPassword(string $password): string
    {
        return Hash::make($password);
    }
}
