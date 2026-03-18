<?php
// Start the session
session_start();

// Include database connection file
require_once __DIR__ . "/config/db_connection.php";

// Define variables and initialize with empty values
$password = $reenter_password = "";
$password_err = $reenter_password_err = "";

// Get token from URL
$token = $_GET['token'] ?? '';

// Check if token is valid
$sql = "SELECT email FROM password_resets WHERE token = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $param_token);
    $param_token = $token;

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($email);
            $stmt->fetch();
        } else {
            echo "Invalid or expired token.";
            exit();
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
        exit();
    }
    $stmt->close();
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate re-entered password
    if (empty(trim($_POST["reenter_password"]))) {
        $reenter_password_err = "Please re-enter your password.";
    } else {
        $reenter_password = trim($_POST["reenter_password"]);
        if ($password != $reenter_password) {
            $reenter_password_err = "Passwords did not match.";
        }
    }

    // Check input errors before updating the password in the database
    if (empty($password_err) && empty($reenter_password_err)) {

        // Prepare an update statement
        $sql_update = "UPDATE userdata SET password = ? WHERE email = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ss", $param_password, $param_email);
            $param_password = $password;
            $param_email = $email;

            if ($stmt_update->execute()) {
                // Delete the token from password_resets table
                $sql_delete = "DELETE FROM password_resets WHERE token = ?";
                if ($stmt_delete = $conn->prepare($sql_delete)) {
                    $stmt_delete->bind_param("s", $param_token);
                    $param_token = $token;
                    $stmt_delete->execute();
                }

                echo '<script>alert("Password reset successful. Please login."); window.location.href = "LoginSignup.php";</script>';
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt_update->close();
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
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2><center>Reset Password</center></h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <input type="password" name="password" placeholder="Enter new password" required>
            <span><?php echo $password_err; ?></span>
            <input type="password" name="reenter_password" placeholder="Re-enter new password" required>
            <span><?php echo $reenter_password_err; ?></span>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
