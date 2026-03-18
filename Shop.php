<?php
// Start the session
session_start();
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
            position: relative; /* Ensure the cart notification position */
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .content {
            text-align: center;
            padding: 20px;
        }

        .content h1 {
            margin-top: 0;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .product {
            background-color: #F1E8DA;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .product:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .product img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .product h3 {
            font-size: 18px;
            margin-bottom: 3px;
        }

        .product p {
            margin-bottom: 10px;
            font-size: 12px;
            margin-top: 2px;
            color: #828282;
        }

        .product-price {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .product-actions {
            display: flex;
            flex-direction: column; /* Align buttons below each other */
            align-items: center;
            margin-top: auto; /* Push actions to the bottom */
        }

        .quantity-input {
            display: flex;
            align-items: center;
            margin-bottom: 5px; /* Adjust spacing */
        }

        .quantity-input button {
            background-color: #d6c4aa;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 5px; /* Adjust spacing */
        }

        .quantity-input input {
            width: 30px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .btn-add-to-cart,
        .btn-buy-now {
            background-color: #black;
            color: white;
            border: none;
            padding: 8px 12px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 5px; /* Adjust spacing */
            width: 100px; /* Adjust button width */
        }

        .btn-buy-now {
            background-color: #333;
            margin-left: 5px;
        }

        .btn-add-to-cart:hover,
        .btn-buy-now:hover {
            background-color: #AF0000;
        }

        .footer {
            background-color: #F1E8DA;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }

        .footer .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer .footer-content span {
            font-weight: 200;
            font-style: italic;
            color: #828282;
            font-size: 12px;
            align-content: center;
        }

        .footer .footer-content img {
            height: 40px;
            margin-left: 25px;
        }

        .footer .footer-content .social-icons img {
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
                <li><a href="Shop.php" style="color: #C80000">Products</a></li>
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
            <h1>Cookies Price 2024</h1>
        </div>
        <div class="products">
            <?php
            include __DIR__ . '/config/db_connection.php';

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT id, name_product, image, price, short_desc FROM product";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product' data-id='" . $row["id"] . "'>";
                    echo "<img src='assets/images/" . htmlspecialchars(basename($row["image"])) . "' alt='" . htmlspecialchars($row["name_product"]) . "'>";
                    echo "<h3>" . $row["name_product"] . "</h3>";
                    echo "<p>" . $row["short_desc"] . "</p>";
                    echo "<p class='product-price'><strong>RM" . number_format($row["price"], 2) . "</strong></p>";
                    echo "<div class='product-actions'>";
                    echo "<div class='quantity-input'>";
                    echo "</div>";

                    // Form for Add to Cart
                    echo "<form action='Checkout.php' method='POST' class='add-to-cart-form' onsubmit='return checkLoginStatus()'>";
                    echo "<input type='hidden' name='product_id' value='" . $row["id"] . "'>";
                    echo "<button type='submit' class='btn-add-to-cart' >Add to Cart</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "0 results";
            }
            $conn->close();
            ?>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-content">
            <img src="assets/images/Logo.png" alt="Siti Cookies">
            <span>Copyright@2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a>
                <br>
                <a href="FAQ.php"><span style="font-weight: 900; margin-left: 0px; font-size: 15px">FAQ</span></a>
            </div>
        </div>
    </footer>

    <script>
        function incrementQuantity(button) {
            var input = button.previousElementSibling;
            var newValue = parseInt(input.value) + 1;
            input.value = newValue;
        }

        function decrementQuantity(button) {
            var input = button.nextElementSibling;
            var newValue = parseInt(input.value) - 1;
            if (newValue >= 1) {
                input.value = newValue;
            }
        }

        function checkLoginStatus() {
            <?php if (!isset($_SESSION['username'])): ?>
                alert("Please log in to proceed.");
                window.location.href = "LoginSignup.php";
                return false;
            <?php endif; ?>
            return true;
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
