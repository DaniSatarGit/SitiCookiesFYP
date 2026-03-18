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
    <title>Admin Home - Siti Cookies</title>
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

        .admin-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
        }

        .admin-container h1 {
            margin-bottom: 20px;
        }

        .admin-container .admin-actions {
            display: flex;
            justify-content: space-around;
            width: 100%;
        }

        .admin-actions form {
            display: flex;
            flex-direction: column;
            padding: 20px;
            border-radius: 10px;
            width: 45%;
        }

        .admin-actions form input,
        .admin-actions form select,
        .admin-actions form textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 16px;
        }

        .admin-actions form button {
            padding: 10px;
            border: none;
            background-color: #000;
            color: #fff;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
        }

        .select {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 13px;
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
            background-color: rgba(0,0,0,0.4);
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
        font-size: 10px;
    }

    .admin-container {
        padding: 20px;
    }

    .admin-actions form {
        width: 100%;
        max-width: 100%; /* Ensure forms take full width on smaller screens */
    }
    

    .admin-container .admin-actions {
        display: flex;
        flex-direction: column; /* Adjust for smaller screens */
        align-items: center; /* Center forms */
        width: 100%;
    }
    
    .admin-actions form {
        display: flex;
        flex-direction: column;
        padding: 20px;
        border-radius: 10px;
        width: 100%;
        max-width: 500px; /* Set a max-width for larger screens */
        margin-bottom: 20px;
    }
    }
    
    @media (max-width: 480px) {
        header nav ul li {
            margin-left: 5px;
            margin-right: 5px;
        }
    
        header nav ul li a {
            font-size: 8px;
        }
    
        .content h1 {
            font-size: 24px;
        }
    
        .content p {
            font-size: 14px;
        }
    
        .footer-content span {
            font-size: 10px;
        }
        
    
    .admin-container .admin-actions {
        display: flex;
        flex-direction: column; /* Adjust for smaller screens */
        align-items: center; /* Center forms */
        width: 100%;
    }
    
    .admin-actions form {
        display: flex;
        flex-direction: column;
        padding: 20px;
        border-radius: 10px;
        width: 100%;
        max-width: 500px; /* Set a max-width for larger screens */
        margin-bottom: 20px;
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
                <li><a href="AdminHome.php" style="color:#C80000">Products</a></li>
                <li><a href="AdminOrder.php">Order</a></li>
                <li><a href="AdminDashboard.php">Dashboard</a></li>
                <li><a href="AdminComment.php">Comment</a></li>
                <li><a href="#" class="login-signup" id="logoutBtn">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="admin-container">
            <?php
            if (isset($_GET['success'])) {
                if ($_GET['success'] == 'add') {
                    echo "<p>Product added successfully!</p>";
                } elseif ($_GET['success'] == 'update') {
                    echo "<p>Product updated successfully!</p>";
                } elseif ($_GET['success'] == 'delete') {
                    echo "<p>Product deleted successfully!</p>";
                }
            }
            ?>
            <h1 style="margin-top: 0px;">Admin Home</h1>
            <div class="admin-actions">
                <form action="actions/add_product.php" method="POST" enctype="multipart/form-data">
                    <h2>Add Product</h2>
                    <input type="text" name="name_product" placeholder="Name Product" required>
                    <input type="file" name="image" required>
                    <input type="number" name="quantity" placeholder="Quantity" required>
                    <input type="text" name="price" placeholder="Price (0.00)" required>
                    <input type="text" name="short_desc" placeholder="Short Description" required>
                    <button type="submit">Submit</button>
                </form>
                <form action="actions/update_product.php" method="POST" enctype="multipart/form-data">
                    <h2>Edit Product</h2>
                    <select name="product_id" required>
                        <option value="" disabled selected>Select Product</option>
                        <?php
                        include __DIR__ . '/config/db_connection.php';
                        $result = $conn->query("SELECT id, name_product FROM product");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name_product']}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="name_product" placeholder="Name Product" required>
                    <input type="number" name="quantity" placeholder="Quantity" required>
                    <input type="text" name="price" placeholder="Price (0.00)" required>
                    <input type="text" name="short_desc" placeholder="Short Description" required>
                    <button type="submit">Submit</button>
                </form>
            
            <form action="actions/delete_product.php" method="POST" style="margin-top: 20px;">
                <h2>Remove Product</h2>
                <select name="product_id" required>
                    <option value="" disabled selected>Select Product</option>
                    <?php
                    $result = $conn->query("SELECT id, name_product FROM product");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name_product']}</option>";
                    }
                    ?>
                </select>
                <button type="submit">Remove</button>
            </form>
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
                <a href="AdminFAQ.php"><span style="font-weight: 900; margin-left: px; font-size: 15px;">FAQ</span></a>
            </div>
        </div>
    </footer>

    <!-- Modal -->
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
