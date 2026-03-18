<?php
session_start();

// Database connection
include __DIR__ . '/config/db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data
$user_email = '';
$user_password_hash = '';
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT email, password FROM userdata WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $stmt->bind_result($user_email, $user_password_hash);
    $stmt->fetch();
    $stmt->close();
}

// Change password logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Verify the old password
    if (password_verify($old_password, $user_password_hash)) {
        // Hash the new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_stmt = $conn->prepare("UPDATE userdata SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $new_password_hash, $_SESSION['username']);
        $update_stmt->execute();
        $update_stmt->close();
        echo "<script>alert('Password changed successfully!');</script>";
    } else {
        echo "<script>alert('Old password is incorrect.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>User Profile - Siti Cookies Shop</title>
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .content {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            background-color: #F1E8DA;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            margin-bottom: 20px;
            color: #black;
        }

        .profile-info, .change-password {
            background-color: #FCF7F0;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .profile-info p, .change-password label {
            margin: 10px 0;
            font-size: 16px;
        }

        .profile-info strong {
            color: #black;
        }

        input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #000;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #555;
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
            min-width: 100px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            left: 50%;
            transform: translateX(-50%); /* Center the dropdown */
            background-color: #F1E8DA;
            min-width: 60px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px; /* Adjust this value for desired spacing */
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            text-align: center;
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

        button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            background-color: #000;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background-color: #555;
        }
        
        @media (max-width: 768px) {
            header nav ul {
                flex-wrap: wrap; /* Allow items to wrap instead of stacking */
                justify-content: center; /* Center items in the navbar */
            }
        
            header nav ul li {
                margin-left: 5px; /* Adjust spacing for smaller screens */
                margin-right: 5px; /* Add right margin for spacing */
            }
        
            header nav ul li a {
                font-size: 15px; /* Smaller font size for mobile */
            }
        }
        
        @media (max-width: 480px) {
            header nav ul li {
                margin-left: 5px; /* Further adjust spacing */
                margin-right: 5px; /* Add right margin for spacing */
            }
        
            header nav ul li a {
                font-size: 11px; /* Ensure font size is small */
            }
        
            .content h1 {
                font-size: 24px; /* Reduce heading size */
            }
        
            .content p {
                font-size: 14px; /* Smaller font size */
            }
        
            .footer-content span {
                font-size: 10px; /* Smaller footer text */
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
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="login-signup"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                        <div class="dropdown-content">
                            <a href="Profile.php">Profile</a>
                            <a href="javascript:void(0)" onclick="confirmLogout()">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="LoginSignup.php" class="login-signup">Login / SignUp</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">
        <div class="content">
            <h1>User Profile</h1>
            <div class="profile-info">
                <p><strong>Username:</strong> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email ? $user_email : 'Not available'); ?></p>
            </div>
            <div id="logoutModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Are you sure you want to logout?</h2>
                    <button onclick="logout()">Yes</button>
                    <button onclick="closeModal()">No</button>
                </div>
            </div>
            <div class="change-password">
                <h2>Change Password</h2>
                <form method="POST" action="">
                    <div class="password-container">
                        <label for="old_password">Old Password:</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    <input type="checkbox" id="showPassword" onclick="togglePassword()">Show Password<br><br>
                    <div class="password-container">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <input type="submit" name="change_password" value="Change Password">
                </form>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer-content">
            <img src="assets/images/Logo.png" alt="Siti Cookies">
            <span>Copyright @2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a>
                <br>
                <a href="FAQ.php"><span style="font-weight: 900; margin-left: 0px; font-size: 15px">FAQ</span></a>
            </div>
        </div>
    </footer>
    <script>
        function togglePassword() {
            var oldPassword = document.getElementById("old_password");
            var newPassword = document.getElementById("new_password");
            if (oldPassword.type === "password") {
                oldPassword.type = "text";
                newPassword.type = "text";
            } else {
                oldPassword.type = "password";
                newPassword.type = "password";
            }
        }

        function confirmLogout() {
            document.getElementById("logoutModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        function logout() {
            window.location.href = "actions/logout.php"; // Redirect to logout
        }

        // Close the modal if the user clicks outside of it
        window.onclick = function(event) {
            let modal = document.getElementById("logoutModal");
            if (event.target == modal) {
                closeModal();
            }
        };
    </script>
</body>
</html>
