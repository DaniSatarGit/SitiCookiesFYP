<?php
// Start the session
session_start();

// Include database connection file
require_once __DIR__ . "/config/db_connection.php";

// Define variables and initialize with empty values
$email = $email_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);

        // Check if email exists in the database
        $sql = "SELECT email FROM userdata WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    // Email exists, generate a unique token
                    $token = bin2hex(random_bytes(50));
                    $sql_insert = "INSERT INTO password_resets (email, token) VALUES (?, ?)";

                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        $stmt_insert->bind_param("ss", $param_email, $param_token);
                        $param_email = $email;
                        $param_token = $token;

                        if ($stmt_insert->execute()) {
                            // Send email
                            $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
                            $to = $email;
                            $subject = "Password Reset";
                            $message = "Click on the following link to reset your password: $reset_link";
                            $headers = "From: no-reply@yourdomain.com";

                            if (mail($to, $subject, $message, $headers)) {
                                echo '<script>alert("Password reset link has been sent to your email."); window.location.href = "LoginSignup.php";</script>';
                            } else {
                                echo "Failed to send email.";
                            }
                        } else {
                            echo "Something went wrong. Please try again later.";
                        }
                        $stmt_insert->close();
                    }
                } else {
                    $email_err = "No account found with that email.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <span><?php echo $email_err; ?></span>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
