<?php
// Start the session
session_start();
include __DIR__ . '/config/db_connection.php';

$query = "SELECT * FROM comment ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Admin Comment</title>
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
            cursor: pointer;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0px;
        }

        .content {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
        }

        .content h1 {
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 20px;
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

        /* Modal CSS */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #FCF7F0;
            margin: 15% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 15px;
        }

        .modal-header,
        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
        }

        .modal-footer button {
            padding: 10px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .modal-footer .confirm {
            background-color: #f44336;
            color: white;
        }

        .modal-footer .cancel {
            background-color: #ccc;
        }

        .comments {
            margin-top: 20px;
            text-align: left; /* Align text to the left */
        }

        .comment {
            background-color: #fff; /* Lighter background */
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            transition: transform 0.2s; /* Smooth hover effect */
        }

        .comment:hover {
            transform: scale(1.02); /* Slightly enlarge on hover */
        }

        .comment .user {
            font-weight: bold;
            color: #333; /* Distinctive color for the username */
        }

        .comment .timestamp {
            font-size: 0.85em;
            color: #828282; /* Grey color for timestamp */
        }

        .comment p {
            margin: 5px 0; /* Add space between paragraphs */
        }

        .comment strong {
            color: #333; /* Darker color for strong text */
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
                font-size: 10px; /* Smaller font size for mobile */
            }
        }
        
        @media (max-width: 480px) {
            header nav ul li {
                margin-left: 5px; /* Further adjust spacing */
                margin-right: 5px; /* Add right margin for spacing */
            }
        
            header nav ul li a {
                font-size: 8px; /* Ensure font size is small */
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
            <a href="AdminHome.php"><img src="assets/images/Logo.png" alt="Siti Cookies"></a>
        </div>
        <nav>
            <ul>
                <li><a href="AdminHome.php">Products</a></li>
                <li><a href="AdminOrder.php">Order</a></li>
                <li><a href="AdminDashboard.php">Dashboard</a></li>
                <li><a href="AdminComment.php" style="color:#C80000">Comment</a></li>
                <li><a href="#" class="login-signup" id="logoutBtn">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <div class="content">
            <h1>Admin Comment</h1>
            <div class="comments">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="comment">
                            <p class="user"><?php echo htmlspecialchars($row['name']); ?> <br><span class="timestamp"><?php echo $row['created_at']; ?></span></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                            <p><?php echo htmlspecialchars($row['message']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No comments available.</p>
                <?php endif; ?>
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
                <a href="AdminFAQ.php"><span style="font-weight: 900; margin-left: 0px; font-size: 15px">FAQ</span></a>
            </div>
        </div>
    </footer>
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Logout</h2>
            </div>
            <p>Are you sure you want to logout?</p>
            <div class="modal-footer">
                <button class="confirm" id="confirmBtn">Yes</button>
                <button class="cancel" id="cancelBtn">No</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('logoutBtn').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('logoutModal').style.display = 'block';
        });

        document.getElementById('cancelBtn').addEventListener('click', function() {
            document.getElementById('logoutModal').style.display = 'none';
        });

        document.getElementById('confirmBtn').addEventListener('click', function() {
            window.location.href = 'actions/logout.php';
        });

        window.onclick = function(event) {
            if (event.target == document.getElementById('logoutModal')) {
                document.getElementById('logoutModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>
