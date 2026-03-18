<?php
session_start();

// Set the time zone to Malaysia Time (MYT)
date_default_timezone_set('Asia/Kuala_Lumpur');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/config/db_connection.php';

// Adding product to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $query = "SELECT * FROM product WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $product['quantity'] = 1; // Default quantity if not provided
        $_SESSION['cart'][] = $product;
    }
}

// Removing product from cart
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $remove_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
}

// Placing order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $username = $_SESSION['username'];
    $products = $_SESSION['cart'];
    $address = htmlspecialchars($_POST['address']);
    $state = htmlspecialchars($_POST['state']);
    $postcode = htmlspecialchars($_POST['postcode']);
    $city = htmlspecialchars($_POST['city']);
    $time_order = date('Y-m-d H:i:s');
    $payment_method = htmlspecialchars($_POST['payment']);
    $total = $_POST['total'];

    $receipt = '';
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/assets/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $receipt_name = basename($_FILES['receipt']['name']);
        $receipt_path = $upload_dir . $receipt_name;
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $receipt_path)) {
            $receipt = $conn->real_escape_string('assets/uploads/' . $receipt_name);
        } else {
            die("Error uploading file.");
        }
    }

    $conn->begin_transaction(); // Start transaction

    try {
        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (username, address, state, postcode, city, time_order, payment_method, receipt, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssd", $username, $address, $state, $postcode, $city, $time_order, $payment_method, $receipt, $total);
        if (!$stmt->execute()) {
            throw new Exception("Error executing query: " . $stmt->error);
        }

        $order_id = $conn->insert_id; // Get the inserted order ID

        // Insert into order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product, quantity) VALUES (?, ?, ?)");
        foreach ($products as $product) {
            $product_name = $product['name_product'];
            $quantity = isset($product['quantity']) ? $product['quantity'] : 1; // Get quantity from cart or default to 1
            $stmt->bind_param("isi", $order_id, $product_name, $quantity);
            if (!$stmt->execute()) {
                throw new Exception("Error executing query: " . $stmt->error);
            }
        }

        $conn->commit(); // Commit transaction

        unset($_SESSION['cart']);
        header("Location: OrderSuccess.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction if any query fails
        die("Transaction failed: " . $e->getMessage());
    }
}
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Siti Cookies Checkout</title>
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

        .checkout-title {
            text-align: center;
            font-size: 24px;
            margin: 20px 0;
        }

        .products {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 0 20px;
        }

        .product-item {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .product-item img {
            height: 50px;
            margin-right: 10px;
            margin-left: 15px;
        }

        .product-info {
            flex-grow: 1;
        }

        .product-title {
            font-size: 16px;
            font-weight: bold;
        }

        .product-description {
            font-size: 14px;
            color: gray;
        }

        .product-price {
            font-size: 14px;
            font-weight: bold;
            margin-right: 10px;
        }

        .quantity-input {
            display: flex;
            align-items: center;
        }

        .quantity-input button {
            background-color: #d6c4aa;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 5px;
        }

        .quantity-input input {
            width: 40px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            text-align: center;
            margin: 0 3px;
        }

        .order-summary {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .order-summary h3 {
            margin-top: 0;
        }

        .order-summary div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .continue-btn {
            background-color: #F1E8DA;
            color: black;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%;
            border-radius: 10px;
            font-weight: bold;
        }

        .address, .payment-method {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 0 20px;
            margin: 20px 0;
        }

        .address input, .payment-method input, .payment-method label {
            width: calc(50% - 10px);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: bold;
            background-color: #F1E8DA;
        }

        .payment-method input[type="radio"] {
            margin-right: 5px;
        }

        .instructions {
            display: none; /* Hide by default */
            margin: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .instructions h2 {
            margin-top: 0;
        }

        .instructions p {
            margin: 10px 0;
        }

        footer {
            background-color: #F1E8DA;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
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

        /* Styles for selected product item */
        .product-checked {
            background-color: #FCE8D8; /* Example background color for selected product */
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

        .upload-form {
            margin: 20px;
            background-color: #FCF7F0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .upload-form input[type="file"] {
            margin: 10px 0;
        }

        .alert-message {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }

        .alert-content {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%; /* Responsive width */
            max-width: 400px; /* Max width */
        }

        .alert-content h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .alert-content button {
            margin-top: 20px;
            background-color: #C80000; /* Alert button color */
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .alert-content button:hover {
            background-color: #a00000; /* Darker on hover */
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
        
        @media (max-width: 360px) {
            header nav ul li {
                margin-left: 5px; /* Further adjust spacing */
                margin-right: 5px; /* Add right margin for spacing */
            }
        
            header nav ul li a {
                font-size: 11px; /* Ensure font size is small */
            }
        
            .content h1 {
                font-size: 20px; /* Reduce heading size */
            }
        
            .content p {
                font-size: 10px; /* Smaller font size */
            }
        
            .footer-content span {
                font-size: 7px; /* Smaller footer text */
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
                <li><a href="Checkout.php" style="color:#C80000">Cart</a></li>
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
    <main>
        <h2 class="checkout-title">CHECK OUT</h2>
        <div class="products">
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <div class="product-item" id="product<?php echo $index; ?>">
                        <input type="checkbox" checked onchange="toggleProduct('product<?php echo $index; ?>')">
                        <img src="<?php echo htmlspecialchars('assets/images/' . basename($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name_product']); ?>">
                        <div class="product-info">
                            <div class="product-title"><?php echo htmlspecialchars($item['name_product']); ?></div>
                            <div class="product-description"><?php echo htmlspecialchars($item['short_desc']); ?></div>
                        </div>
                        <div class="product-price">RM<?php echo number_format($item['price'], 2); ?></div>
                        <div class="quantity-input">
                            <button onclick="decrementQuantity('<?php echo $index; ?>')">-</button>
                            <input type="number" value="1" min="1" onchange="updateOrderSummary()">
                            <button onclick="incrementQuantity('<?php echo $index; ?>')">+</button>
                            <button onclick="removeFromCart('<?php echo $item['id']; ?>')">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No items in the cart.</p>
            <?php endif; ?>
        </div>
        <div class="order-summary">
            <h3>Order summary</h3>
            <div>
                <span>Subtotal</span>
                <span id="subtotal">RM0.00</span>
            </div>
            <div>
                <span>Shipping</span>
                <span>RM4.90</span>
            </div>
            <div>
                <span>Tax</span>
                <span>RM1.00</span>
            </div>
            <div>
                <span><strong>Total</strong></span>
                <strong><span id="total">RM0.00</span></strong>
            </div>
            <button class="continue-btn" onclick="continueToPayment()">Place Order</button>
        </div>
        <div class="address">
            <input type="text" id="address" placeholder="Address">
            <input type="text" id="state" placeholder="State">
            <input type="text" id="postcode" placeholder="Postcode">
            <input type="text" id="city" placeholder="City">
        </div>
        <div class="payment-method">
            <label>
                <input type="radio" name="payment" value="Cash On Delivery" id="cash" checked>
                Cash on Delivery
            </label>
            <label>
                <input type="radio" name="payment" value="Online Banking" id="online">
                Online Banking
            </label>
        </div>
        
    <div class="instructions">
    <h2>Online Banking Payment Instructions</h2>
    <p>Please follow the steps below to complete your payment:</p>
    <ol>
        <li>Log in to your online banking account through your bank's official website or mobile app.</li>
        <li>Navigate to the 'Transfer' or 'Payments' section. This may vary slightly depending on your bank.</li>
        <li>Initiate a new transfer and enter the payment details as follows:</li>
        <ul>
            <li><strong>Bank Name:</strong> Maybank</li>
            <li><strong>Account Number:</strong> 123456789</li>
            <li><strong>Account Holder Name:</strong> Siti Cookies Shop</li>
        </ul>
        <li>Double-check the entered details to ensure they are correct. Any mistakes may delay the processing of your order.</li>
        <li>Enter the total amount of your purchase. Make sure this matches the amount shown on your order confirmation.</li>
        <li>Review the details of your transfer and confirm the payment. Note down the transaction reference number for your records.</li>
        <li>Take a screenshot or save a copy of your transfer receipt. This will be needed for verification purposes.</li>
    </ol>
    <p>After completing the transfer, please upload your receipt using the form below. Make sure the receipt clearly shows the following details:</p>
    <ul>
        <li>The transaction reference number</li>
        <li>The date of the transaction</li>
        <li>The amount transferred</li>
        <li>The account holder's name (Siti Cookies Shop)</li>
    </ul>
    <p>Upload your receipt using the form below:</p>
        <div class="upload-form">
            <form id="uploadForm" method="post" enctype="multipart/form-data">
                <label for="receipt">Upload Receipt:</label>
                <input type="file" name="receipt" id="receipt" required>
            </form>
        </div>
    </div>
        <form id="checkout-form" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="subtotal" id="hidden-subtotal" value="">
            <input type="hidden" name="total" id="hidden-total" value="">
            <input type="hidden" name="address" id="hidden-address" value="">
            <input type="hidden" name="state" id="hidden-state" value="">
            <input type="hidden" name="postcode" id="hidden-postcode" value="">
            <input type="hidden" name="city" id="hidden-city" value="">
            <input type="hidden" name="payment" id="hidden-payment" value="">
            <input type="hidden" name="receipt" id="hidden-receipt" value="">
            <input type="hidden" name="place_order" value="1">
        </form>
    </main>
    <footer>
        <div class="footer-content">
            <img src="assets/images/Logo.png" alt="Siti Cookies">
            <span>Copyright @2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a>
                <br>
                <a href="FAQ.php"><span style="font-weight: 900; font-size: 15px;">FAQ</span></a>
            </div>
        </div>
    </footer>
        <script>
            function toggleProduct(productId) {
                const productItem = document.getElementById(productId);
                productItem.classList.toggle('product-checked');
                updateOrderSummary();
            }
        
            function updateOrderSummary() {
                let subtotal = 0;
                const productItems = document.querySelectorAll('.product-item');
                productItems.forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox.checked) {
                        const price = parseFloat(item.querySelector('.product-price').innerText.replace('RM', ''));
                        const quantity = parseInt(item.querySelector('.quantity-input input').value);
                        subtotal += price * quantity;
                    }
                });
                document.getElementById('subtotal').innerText = 'RM' + subtotal.toFixed(2);
                const shipping = 4.90;
                const tax = 1.00;
                const total = subtotal + shipping + tax;
                document.getElementById('total').innerText = 'RM' + total.toFixed(2);
                document.getElementById('hidden-subtotal').value = subtotal.toFixed(2);
                document.getElementById('hidden-total').value = total.toFixed(2);
            }
        
            function incrementQuantity(index) {
                const quantityInput = document.querySelector(`#product${index} .quantity-input input`);
                quantityInput.value = parseInt(quantityInput.value) + 1;
                updateOrderSummary();
            }
        
            function decrementQuantity(index) {
                const quantityInput = document.querySelector(`#product${index} .quantity-input input`);
                if (quantityInput.value > 1) {
                    quantityInput.value = parseInt(quantityInput.value) - 1;
                    updateOrderSummary();
                }
            }
        
            function removeFromCart(productId) {
                if (confirm("Are you sure you want to remove this item from the cart?")) {
                    window.location.href = "?remove_id=" + productId;
                }
            }
            
            function validateCart() {
                // This function should be adapted based on how your cart is structured.
                var cartItems = document.querySelectorAll('.product-item input[type="checkbox"]');
                var cartNotEmpty = false;
            
                cartItems.forEach(function(item) {
                    if (item.checked) {
                        cartNotEmpty = true;
                    }
                });
            
                return cartNotEmpty;
            }
        
            function continueToPayment() {
                <?php if (!isset($_SESSION['username'])): ?>
                    alert("Please log in to proceed.");
                    window.location.href = "LoginSignup.php";
                    return false;
                <?php endif; ?>
            
                // Check if cart is not empty
                if (!validateCart()) {
                    alert('Your cart is empty. Please add items to your cart before placing an order.');
                    return;
                }
            
                const address = document.getElementById('address').value;
                const state = document.getElementById('state').value;
                const postcode = document.getElementById('postcode').value;
                const city = document.getElementById('city').value;
                const payment = document.querySelector('input[name="payment"]:checked').value;
            
                if (!address || !state || !postcode || !city) {
                    alert('Please fill in all address fields.');
                    return;
                }
            
                document.getElementById('hidden-address').value = address;
                document.getElementById('hidden-state').value = state;
                document.getElementById('hidden-postcode').value = postcode;
                document.getElementById('hidden-city').value = city;
                document.getElementById('hidden-payment').value = payment;
                document.getElementById('checkout-form').submit();
            }
            
            function confirmLogout() {
                document.getElementById('logoutModal').style.display = 'block';
            }
        
            function closeModal() {
                document.getElementById('logoutModal').style.display = 'none';
            }
        
            function logout() {
                window.location.href = 'actions/logout.php';
            }
            
            function toggleInstructions() {
                const onlineChecked = document.getElementById('online').checked;
                const instructions = document.querySelector('.instructions');
                instructions.style.display = onlineChecked ? 'block' : 'none';
            }
        
            // Add event listeners to radio buttons
            document.getElementById('cash').addEventListener('change', toggleInstructions);
            document.getElementById('online').addEventListener('change', toggleInstructions);
        
            // Add event listeners to checkboxes to update the order summary when their state changes
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateOrderSummary);
            });
        
            // Add event listeners to quantity inputs to update the order summary when their values change
            document.querySelectorAll('.quantity-input input').forEach((input, index) => {
                input.addEventListener('change', () => updateOrderSummary(index));
            });
        
            // Call once to set initial state
            toggleInstructions();
        
            // Initial order summary update
            updateOrderSummary();
        </script>

</body>
</html>
