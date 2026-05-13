<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Order Success - Siti Cookies</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .main-content {
            padding: 50px;
            background-color: #FCF7F0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .main-content p {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .main-content img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .continue-btn {
            background-color: #F1E8DA;
            color: black;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
        }

        .continue-btn:hover {
            background-color: #d6c4aa;
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
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 5000);
    </script>
</head>
<body>
    <div class="main-content">
        <h1>Order Placed Successfully!</h1>
        <img src="assets/images/accept.png" alt="Order Success" style="width: 100px;"> <!-- Replace with your image file -->
        <p>Thank you for your order! We are thrilled to have the opportunity to serve you. Our team at Siti Cookies is committed to providing you with the best products and customer service experience.</p>
        <p>Your order has been received and is currently being processed. Once your order is shipped, you will receive a confirmation email with the details of your order and tracking information. Please allow 1-2 business days for order processing.</p>
        <p>If you have any questions or need further assistance, feel free to contact our customer support team at support@siticookies.com or call us at +123-456-7890. We are here to help you and ensure you have a delightful experience with us.</p>
        <p>We hope you enjoy our cookies as much as we enjoy making them for you. Thank you for choosing Siti Cookies!</p>
        <a href="index.php" class="continue-btn">Continue Shopping</a>
        <p>This page will redirect to the home page in 5 seconds.</p>
    </div>
</body>
</html>
