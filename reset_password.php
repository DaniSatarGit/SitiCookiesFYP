<?php
require_once __DIR__ . '/includes/bootstrap.php';

$password = '';
$reenter_password = '';
$password_err = '';
$reenter_password_err = '';
$email = '';
$token = $_GET['token'] ?? '';

$stmt = $conn->prepare('SELECT email FROM password_resets WHERE token = ? LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    $stmt->close();
    exit('Invalid or expired token.');
}

$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $reenter_password = trim($_POST['reenter_password'] ?? '');

    if ($password === '') {
        $password_err = 'Please enter a password.';
    } elseif (strlen($password) < 6) {
        $password_err = 'Password must have at least 6 characters.';
    }

    if ($reenter_password === '') {
        $reenter_password_err = 'Please re-enter your password.';
    } elseif ($password !== $reenter_password) {
        $reenter_password_err = 'Passwords did not match.';
    }

    if ($password_err === '' && $reenter_password_err === '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare('UPDATE userdata SET password = ? WHERE email = ?');
        $update_stmt->bind_param('ss', $password_hash, $email);

        if ($update_stmt->execute()) {
            $delete_stmt = $conn->prepare('DELETE FROM password_resets WHERE token = ?');
            $delete_stmt->bind_param('s', $token);
            $delete_stmt->execute();
            $delete_stmt->close();

            echo '<script>alert("Password reset successful. Please login."); window.location.href = "LoginSignup.php";</script>';
        } else {
            echo 'Something went wrong. Please try again later.';
        }

        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2><center>Reset Password</center></h2>
        <form action="<?php echo h($_SERVER['PHP_SELF']); ?>?token=<?php echo h($token); ?>" method="post">
            <input type="password" name="password" placeholder="Enter new password" required>
            <span><?php echo h($password_err); ?></span>
            <input type="password" name="reenter_password" placeholder="Re-enter new password" required>
            <span><?php echo h($reenter_password_err); ?></span>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
