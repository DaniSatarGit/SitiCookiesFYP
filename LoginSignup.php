<?php
// Start the session
session_start();

// Start output buffering
ob_start();

// Include database connection file
require_once __DIR__ . "/config/db_connection.php";

// Define variables and initialize with empty values
$username = $email = $password = $reenter_password = "";
$username_err = $email_err = $password_err = $reenter_password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // If the form is for registering a new user
    if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['reenter_password'])) {

        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter a username.";
        } else {
            // Prepare a select statement to check if the username already exists
            $sql_check = "SELECT username FROM userdata WHERE username = ?";

            if ($stmt_check = $conn->prepare($sql_check)) {
                $stmt_check->bind_param("s", $param_username);

                // Set parameters
                $param_username = trim($_POST["username"]);

                // Attempt to execute the prepared statement
                if ($stmt_check->execute()) {
                    $stmt_check->store_result();

                    if ($stmt_check->num_rows == 1) {
                        $username_err = "This username is already taken.";
                    } else {
                        $username = trim($_POST["username"]);
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                $stmt_check->close();
            }
        }

        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter an email address.";
        } else {
            $email = trim($_POST["email"]);
        }

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

        // Check input errors before inserting into database
        if (empty($username_err) && empty($email_err) && empty($password_err) && empty($reenter_password_err)) {

            // Prepare an insert statement
            $sql_insert = "INSERT INTO userdata (username, email, password) VALUES (?, ?, ?)";

            if ($stmt_insert = $conn->prepare($sql_insert)) {
                // Bind variables to the prepared statement as parameters
                $stmt_insert->bind_param("sss", $param_username, $param_email, $param_password);

                // Set parameters
                $param_username = $username;
                $param_email = $email;
                // Hash the password before saving it to the database
                $param_password = password_hash($password, PASSWORD_BCRYPT);

                // Attempt to execute the prepared statement
                if ($stmt_insert->execute()) {
                    // Redirect to login page with success message
                    echo '<script>alert("Registration successful, please login."); window.location.href = "LoginSignup.php";</script>';
                } else {
                    echo "Something went wrong. Please try again later.";
                }
                // Close statement
                $stmt_insert->close();
            }
        }

        // Close connection
        $conn->close();
    }

    // If the form is for logging in
    elseif (isset($_POST['login_username']) && isset($_POST['login_password'])) {
        $username = trim($_POST['login_username']);
        $password = trim($_POST['login_password']);

        // Check if username is empty
        if (empty($username)) {
            echo '<script>alert("Please enter username.");</script>';
        } else {
            $username = trim($_POST["login_username"]);
        }

        // Check if password is empty
        if (empty($password)) {
            echo '<script>alert("Please enter your password.");</script>';
        } else {
            $password = trim($_POST["login_password"]);
        }

        // Validate credentials
        if (!empty($username) && !empty($password)) {
            // Check if admin
            if ($username === 'SitiAdmin' && $password === 'sitiadmin123') {
                $_SESSION['username'] = $username;
                header("location: AdminHome.php");
                exit();
            }

            // Prepare a select statement
            $sql = "SELECT username, password FROM userdata WHERE username = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $param_username);

                // Set parameters
                $param_username = $username;

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();

                    // Check if username exists, if yes then verify password
                    if ($stmt->num_rows == 1) {
                        // Bind result variables
                        $stmt->bind_result($username, $stored_password);
                        if ($stmt->fetch()) {
                            // Verify password
                            if (password_verify($password, $stored_password)) {
                                // Password is correct, start a new session and redirect to home page
                                $_SESSION['username'] = $username;
                                header("location: index.php");
                            } else {
                                // Display an error message if password is not valid
                                echo '<script>alert("The password you entered was not valid.");</script>';
                            }
                        }
                    } else {
                        // Display an error message if username doesn't exist
                        echo '<script>alert("No account found with that username.");</script>';
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        }

        // Close connection
        $conn->close();
    }
}

// Flush the output buffer
ob_end_flush();
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

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #F1E8DA;
        }

        header .logo {
            display: flex;
            align-items: center;
        }

        header .logo img {
            height: 40px;
            margin-right: 10px;
            margin-left: 25px;
        }

        header nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
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

        header nav ul li a.login-signup {
            background-color: #000;
            color: #fff;
            padding: 11px 25px;
            border-radius: 5px;
            font-size: 10px;
        }

        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 50px;
        }

        .auth-container h2 {
            margin-bottom: 20px;
        }

        .auth-container form {
            display: flex;
            flex-direction: column;
        }

        .auth-container input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .auth-container button {
            padding: 10px;
            border: none;
            background-color: #000;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .auth-container .login {
            width: 560px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-container .login a {
            font-size: 14px; 
            color: #555;
            font-weight: bold;
        }
        
        .auth-container .signup {
            width: 560px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-container .signup p {
            font-size: 14px; 
            color: #555;     
        }

        .auth-container .signup p a {
            color: black;
            font-weight: bold;
        }

        footer {
            background-color: #F1E8DA;
            padding: 20px;
            text-align: center;
        }

        footer .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        footer .footer-content span {
            font-weight: 200;
            font-style: italic;
            color: #828282;
            font-size: 12px;
            align-content: center;
        }


        footer .footer-content img {
            height: 40px;
            margin-left: 25px;
        }

        footer .footer-content .social-icons img {
            height: 30px;
            margin-left: 0px;
            margin-right: 0px;
        }

        .message-box {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }
        .message-box h2 {
            margin-top: 0;
        }
        .message-box p {
            margin: 10px 0;
        }
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .button-container a {
            text-decoration: none;
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 0 10px;
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
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

    /* Media Queries for responsiveness */
    @media (max-width: 768px) {
        header nav ul {
            flex-wrap: wrap;
            justify-content: center;
        }
    
        header nav ul li {
            margin-left: 5px;
            margin-right: 5px;
        }
    
        header nav ul li a {
            font-size: 15px;
        }
    
        .auth-container {
            padding: 15px;
        }
    
        .auth-container input {
            font-size: 15px;
        }
    
        .auth-container .login {
            width: 360px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-container .login a {
            font-size: 10px; 
            color: #555;
            font-weight: bold;
        }
        
        .auth-container .signup {
            width: 360px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-container .signup p {
            font-size: 10px; 
            color: #555;     
        }

        .auth-container button {
            font-size: 10px;
            padding: 10px;
        }
    
        .auth-container h2 {
            font-size: 15px;
        }
    }

    @media (max-width: 480px) {
        header nav ul li {
            margin-left: 5px;
            margin-right: 5px;
        }
    
        header nav ul li a {
            font-size: 11px;
        }
    
        .content h1 {
            font-size: 20px;
        }
    
        .content p {
            font-size: 15px;
        }
    
        .auth-container .login {
            width: 360px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-container .login a {
            font-size: 10px; 
            color: #555;
            font-weight: bold;
        }
        
        .auth-container .signup {
            width: 360px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-container .signup p {
            font-size: 10px; 
            color: #555;     
        }

        .footer-content span {
            font-size: 8px;
        }
    
        .auth-container input {
            font-size: 10px;
        }
    
        .auth-container button {
            font-size: 10px;
            padding: 10px;
        }
    
        .auth-container h2 {
            font-size: 15px;
        }
    }
    </style>
</head>
<body>    
    <header>
        <div class="logo">
            <a href="index.php"><img src="assets/images/Logo.png" alt="Siti Cookies"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="Shop.php">Products</a></li>
                <li><a href="Checkout.php">Cart</a></li>
                <li><a href="LoginSignup.php" class="login-signup">Login / SignUp</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modal-title">Title</h2>
                <p id="modal-body">Content goes here.</p>
            </div>
        </div>
        <div class="auth-container">
            <div class="login">
                <h2><center>Login</center></h2>
                <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="text" id="login-username" name="login_username" placeholder="Username" required>
                    <input type="password" id="login-password" name="login_password" placeholder="Password" required>
                    <button type="submit" style="font-weight: 600">Sign In</button>
                </form>
                <p style="color: grey"><a href="forgot_password.php" style="color: black">Forgot Password</a></p>
            </div>
            <div class="signup">
                <h2><center>Sign Up</center></h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="text" name="username" placeholder="Username" required>
                    <span><?php echo $username_err; ?></span>
                    <input type="email" name="email" placeholder="Email" required>
                    <span><?php echo $email_err; ?></span>
                    <input type="password" name="password" placeholder="Password" required>
                    <span><?php echo $password_err; ?></span>
                    <input type="password" name="reenter_password" placeholder="Re-Enter Password" required>
                    <span><?php echo $reenter_password_err; ?></span>
                    <button type="submit" style="font-weight: 600">Sign Up</button>
                </form>
                <p style="color: grey">
                    By clicking Sign Up, you agree to our 
                    <a href="javascript:void(0);" onclick="openModal('Terms of Service', 'These Terms of Service govern your use of our website and services. By using our site, you agree to comply with these terms. Please review them carefully.')">Terms of Service</a> and 
                    <a href="javascript:void(0);" onclick="openModal('Privacy Policy', 'Our Privacy Policy explains how we collect, use, and protect your information. We value your privacy and are committed to safeguarding your data.')">Privacy Policy</a>
                </p>
            </div>
        </div>
    </main>
    <footer>
        <div class="footer-content">
            <img src="assets/images/Logo.png" alt="Siti Cookies">
            <span>Copyright @2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a>
                <br>
                <a href="FAQ.php"><span style="font-weight: 900; margin-left: px; font-size: 15px">FAQ</span></a>
            </div>
        </div>
    </footer>
        <script>
            function openModal(title, body) {
                document.getElementById("modal-title").innerText = title;
                document.getElementById("modal-body").innerText = body;
                document.getElementById("modal").style.display = "block";
            }

            function closeModal() {
                document.getElementById("modal").style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == document.getElementById("modal")) {
                    closeModal();
                }
            }
        </script>
</body>
</html>
