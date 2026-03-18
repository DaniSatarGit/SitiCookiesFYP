<?php
// Start the session
session_start();
include __DIR__ . '/config/db_connection.php'; // Ensure you include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO comment (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    
    if ($stmt->execute()) {
        echo "<script>alert('Comment submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting comment.');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Siti Cookies Shop</title>
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

        .BuyNow {
            padding: 12px;
            border: none;
            background-color: #000;
            color: #fff;
            font-size: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .content {
            text-align: center;
            padding: 20px;
            margin-top: 0px;
        }

        .content h1 {
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 20px;
        }

        .products {
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
        }

        .product {
            width: 30%;
            background-color: #F1E8DA;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-right: 30px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
            border-radius: 5px; 
        }

        .product:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .product h3 {
            font-size: 18px;
            margin-bottom: 3px;
        }

        .product p {
            margin-bottom: 10px;
            font-size: 12px;
            margin-top: 2px;
        }

        .comments {
            background-color: #F1E8DA;
            padding: 20px;
            margin-bottom: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .comment {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #FCF7F0;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment p {
            margin: 0;
        }

        .comment .user {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .comment .message {
            color: #828282;
        }

        .leave-comment {
            margin-bottom: 50px;
        }

        .leave-comment h2 {
            margin-bottom: 20px;
        }

        .leave-comment form {
            display: flex;
            flex-direction: column;
        }

        .leave-comment input,
        .leave-comment textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .leave-comment button {
            width: 150px;
            padding: 10px;
            border: none;
            background-color: #000;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
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

        .banner {
            width: 100%;
            border-radius: 15px;
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
            
            .product {
                width: 40%;
                background-color: #F1E8DA;
                padding: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                margin-right: 30px;
                cursor: pointer;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            
            .leave-comment button {
                width: 100px;
                padding: 10px;
                border: none;
                background-color: #000;
                color: #fff;
                font-size: 12px;
                border-radius: 5px;
                cursor: pointer;
            }
                
            .product h3 {
                font-size: 13px;
                margin-bottom: 3px;
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
            
            .product {
                width: 40%;
                background-color: #F1E8DA;
                padding: 10px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                margin-right: 30px;
                cursor: pointer;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            
            .leave-comment button {
                width: 100px;
                padding: 10px;
                border: none;
                background-color: #000;
                color: #fff;
                font-size: 12px;
                border-radius: 5px;
                cursor: pointer;
            }
                
            .product h3 {
                font-size: 13px;
                margin-bottom: 3px;
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
                <li><a href="index.php" style="color: #C80000">Home</a></li>
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
            <div id="logoutModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Are you sure you want to logout?</h2>
                    <button onclick="logout()">Yes</button>
                    <button onclick="closeModal()">No</button>
                </div>
            </div>
    </header>
    <div class="container">
        <div class="content">
            <h1>SITI COOKIES SHOP</h1>
            <p style="color: #828282">Siti Cookies offers a wide selection of delicious cookies, easy to order online, and exciting latest promotions and discounts for loyal customers!</p>
            <a href="Shop.php"><button type="Submit" class="BuyNow">Buy Now</button></a>
        </div>
        <center><img class="banner" src="assets/images/Banner.png" alt="Banner"></center>
        <h2>Top 3 Best Seller</h2>
        <p style="color : #828282;">Here are the three best-selling cookies at Siti Cookies</p>
        <div class="products">
            <div class="product">
                <img src="assets/images/AlmondLondon.jpg" alt="Almond Delight">
                <h3>Almond London</h3>
                <p style="color: #828282">Almond, chocolate-coated</p>
                <p style="font-weight: bold;">RM30.00</p>
            </div>
            <div class="product">
                <img src="assets/images/TartNenas.jpg" alt="Tart Nenas">
                <h3>Tart Nenas</h3>
                <p style="color: #828282">Pineapple jam tart</p>
                <p style="font-weight: bold;">RM28.00</p>
            </div>
            <div class="product">
                <img src="assets/images/NutellaChocolate.jpg" alt="Kuih Coklat">
                <h3>Nutella Chocolate</h3>
                <p style="color: #828282">Nutella-filled chocolate</p>
                <p style="font-weight: bold;">RM30.00</p>
            </div>
        </div>
        <h2>Comments</h2>
        <div class="comments">
            <div class="comment">
                <p class="user">Iqbal:</p>
                <p class="message">Kuih pelbagai pilihan dan menarik!</p>
                <p><strong>Siti Cookies:</strong> Terima Kasih!</p>
            </div>
            <div class="comment">
                <p class="user">Luqman:</p>
                <p class="message">Kuih pelbagai pilihan dan menarik!</p>
                <p><strong>Siti Cookies:</strong> Terima Kasih!</p>
            </div>
            <div class="comment">
                <p class="user">Aqeel:</p>
                <p class="message">Sedap, lazat dan mudah tempah!</p>
                <p><strong>Siti Cookies:</strong> Terima Kasih atas pujian!</p>
            </div>
        </div>
        <h2>Leave a Comment!</h2>
        <p style="color: #828282">If you have any questions or feedback, we'd love to hear from you!</p>
        <div class="leave-comment">
            <form id="comment-form" method="POST">
                <input type="text" id="name" name="name" placeholder="Name" required>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <textarea id="message" name="message" placeholder="Message" required></textarea>
                <button type="submit">Submit</button>
            </form>
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
                var modal = document.getElementById("logoutModal");
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
    </script>
</body>
</html>
