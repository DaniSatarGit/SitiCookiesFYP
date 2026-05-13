<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

$usernameErr = '';
$emailErr = '';
$passwordErr = '';
$reenterPasswordErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['reenter_password'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $reenterPassword = trim($_POST['reenter_password']);

        if ($username === '') {
            $usernameErr = 'Please enter a username.';
        } else {
            $stmt = $conn->prepare('SELECT username FROM userdata WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $usernameErr = 'This username is already taken.';
            }

            $stmt->close();
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = 'Please enter a valid email address.';
        }

        if ($password === '') {
            $passwordErr = 'Please enter a password.';
        } elseif (strlen($password) < 6) {
            $passwordErr = 'Password must have at least 6 characters.';
        }

        if ($reenterPassword === '') {
            $reenterPasswordErr = 'Please re-enter your password.';
        } elseif ($password !== $reenterPassword) {
            $reenterPasswordErr = 'Passwords did not match.';
        }

        $hasError = $usernameErr || $emailErr || $passwordErr || $reenterPasswordErr;

        if (!$hasError) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('INSERT INTO userdata (username, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $username, $email, $hashedPassword);
            $registered = $stmt->execute();

            if ($registered) {
                set_flash('success', 'Registration successful. Please log in.');
            } else {
                set_flash('error', 'Unable to complete registration right now.');
            }

            $stmt->close();
            redirect('LoginSignup.php');
        }
    }

    if (isset($_POST['login_username'], $_POST['login_password'])) {
        $loginUsername = trim($_POST['login_username']);
        $loginPassword = trim($_POST['login_password']);

        if ($loginUsername === '' || $loginPassword === '') {
            set_flash('error', 'Please enter both username and password.');
            redirect('LoginSignup.php');
        }

        if (verify_admin_credentials($loginUsername, $loginPassword)) {
            login_user($loginUsername, 'admin');
            redirect('AdminHome.php');
        }

        $stmt = $conn->prepare('SELECT username, password FROM userdata WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $loginUsername);
        $stmt->execute();
        $stmt->bind_result($storedUsername, $storedPassword);

        if ($stmt->fetch() && password_verify($loginPassword, $storedPassword)) {
            login_user($storedUsername, 'customer');
            $stmt->close();
            redirect('index.php');
        }

        $stmt->close();
        set_flash('error', 'Invalid username or password.');
        redirect('LoginSignup.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Siti Cookies - Login / SignUp</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FCF7F0;
            color: #333;
        }

        header, footer {
            background-color: #F1E8DA;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo img,
        footer .footer-content img {
            height: 40px;
            margin-left: 25px;
        }

        header nav ul,
        footer .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0;
            padding: 0;
        }

        header nav ul {
            list-style: none;
        }

        header nav ul li {
            margin-left: 40px;
        }

        header nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .login-signup {
            background-color: #000;
            color: #fff;
            padding: 11px 25px;
            border-radius: 5px;
            font-size: 10px;
        }

        .auth-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 60px;
            display: grid;
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 24px;
        }

        .auth-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        }

        .auth-card h2 {
            margin-top: 0;
            text-align: center;
        }

        .auth-card form {
            display: flex;
            flex-direction: column;
        }

        .auth-card input,
        .auth-card button {
            margin-bottom: 14px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #d7d0c6;
            font-size: 15px;
        }

        .auth-card button {
            background-color: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 700;
        }

        .auth-card p,
        .auth-card a,
        .error-text {
            font-size: 14px;
        }

        .error-text {
            color: #C80000;
            margin: -8px 0 10px;
            min-height: 18px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background-color: #F1E8DA;
            min-width: 90px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 450px;
            text-align: center;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        footer .footer-content span {
            font-weight: 200;
            font-style: italic;
            color: #828282;
            font-size: 12px;
        }

        footer .social-icons img {
            height: 30px;
        }

        @media (max-width: 768px) {
            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            header nav ul li {
                margin: 0 5px;
            }

            .auth-container {
                grid-template-columns: 1fr;
                padding: 24px 16px 40px;
            }
        }
    </style>
</head>
<body>
    <?php render_site_header(); ?>
    <?php render_flash(); ?>

    <main>
        <div class="auth-container">
            <div class="auth-card">
                <h2>Login</h2>
                <form action="<?= h($_SERVER['PHP_SELF']); ?>" method="post">
                    <input type="text" name="login_username" placeholder="Username" required>
                    <input type="password" name="login_password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>
                <p><a href="forgot_password.php" style="color:#000;font-weight:700;">Forgot Password</a></p>
            </div>

            <div class="auth-card">
                <h2>Sign Up</h2>
                <form action="<?= h($_SERVER['PHP_SELF']); ?>" method="post">
                    <input type="text" name="username" placeholder="Username" required>
                    <div class="error-text"><?= h($usernameErr); ?></div>
                    <input type="email" name="email" placeholder="Email" required>
                    <div class="error-text"><?= h($emailErr); ?></div>
                    <input type="password" name="password" placeholder="Password" required>
                    <div class="error-text"><?= h($passwordErr); ?></div>
                    <input type="password" name="reenter_password" placeholder="Re-Enter Password" required>
                    <div class="error-text"><?= h($reenterPasswordErr); ?></div>
                    <button type="submit">Sign Up</button>
                </form>
                <p style="color:#666;">By clicking Sign Up, you agree to our <strong>Terms of Service</strong> and <strong>Privacy Policy</strong>.</p>
            </div>
        </div>
    </main>

    <?php render_site_footer('FAQ.php'); ?>
    <?php render_logout_script(); ?>
</body>
</html>
