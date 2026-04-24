<?php
require_once __DIR__ . '/includes/bootstrap.php';

$email = '';
$email_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT email FROM userdata WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $token = bin2hex(random_bytes(50));
            $insert_stmt = $conn->prepare('INSERT INTO password_resets (email, token) VALUES (?, ?)');
            $insert_stmt->bind_param('ss', $email, $token);

            if ($insert_stmt->execute()) {
                $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
                $subject = 'Password Reset';
                $message = "Click on the following link to reset your password: $reset_link";
                $headers = 'From: no-reply@yourdomain.com';

                if (mail($email, $subject, $message, $headers)) {
                    echo '<script>alert("Password reset link has been sent to your email."); window.location.href = "LoginSignup.php";</script>';
                } else {
                    echo 'Failed to send email.';
                }
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            $insert_stmt->close();
        } else {
            $email_err = 'No account found with that email.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2><center>Forgot Password</center></h2>
        <form action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <span><?php echo h($email_err); ?></span>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
