<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/db_connection.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit();
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function current_user(): ?string
{
    return $_SESSION['username'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['username']);
}

function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

function require_login(string $redirectTo = 'LoginSignup.php'): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect($redirectTo);
    }
}

function require_admin(string $redirectTo = 'LoginSignup.php'): void
{
    if (!is_admin()) {
        set_flash('error', 'Admin access is required.');
        redirect($redirectTo);
    }
}

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);

    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

function verify_admin_credentials(string $username, string $password): bool
{
    $adminUsername = env_value('SITI_ADMIN_USERNAME', 'SitiAdmin');
    $adminPassword = env_value('SITI_ADMIN_PASSWORD', 'sitiadmin123');

    return hash_equals($adminUsername, $username) && hash_equals($adminPassword, $password);
}

function login_user(string $username, string $role = 'customer'): void
{
    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
