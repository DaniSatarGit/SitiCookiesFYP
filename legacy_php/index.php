<?php
require_once __DIR__ . '/includes/store.php';
require_once __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        set_flash('error', 'Please fill in all comment fields.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid email address.');
    } elseif (submit_comment($conn, $name, $email, $message)) {
        set_flash('success', 'Comment submitted successfully.');
    } else {
        set_flash('error', 'Unable to submit your comment right now.');
    }

    redirect('index.php');
}

$comments = fetch_comments($conn, 6);
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

        header, footer {
            background-color: #F1E8DA;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo img,
        footer .footer-content img {
            height: 40px;
            margin-left: 25px;
        }

        header nav ul,
        footer .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0;
            padding: 0;
        }

        header nav ul {
            list-style: none;
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

        .login-signup,
        .BuyNow,
        button {
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-signup {
            padding: 11px 25px;
            font-size: 10px;
        }

        .BuyNow {
            padding: 12px 18px;
            font-size: 12px;
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
        }

        .products {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .product,
        .comments,
        .comment {
            background-color: #F1E8DA;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .product {
            padding: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .product img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            border-radius: 8px;
        }

        .comments {
            padding: 20px;
            margin-bottom: 50px;
        }

        .comment {
            background-color: #FCF7F0;
            padding: 15px;
            margin-bottom: 15px;
        }

        .comment .user {
            font-weight: bold;
        }

        .comment .meta,
        .comment .message,
        .subtitle,
        footer .footer-content span {
            color: #828282;
        }

        .leave-comment form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .leave-comment input,
        .leave-comment textarea,
        .leave-comment button {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        .leave-comment button {
            width: 160px;
            border: none;
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
            transform: translateX(-50%);
            background-color: #F1E8DA;
            min-width: 90px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 10px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown:hover .dropdown-content {
            display: block;
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
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 450px;
            text-align: center;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        footer .social-icons img {
            height: 30px;
        }

        @media (max-width: 768px) {
            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            header nav ul li {
                margin: 0 5px;
            }

            .products {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 480px) {
            .products {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php render_site_header('home'); ?>
    <?php render_flash(); ?>

    <div class="container">
        <div class="content">
            <h1>SITI COOKIES SHOP</h1>
            <p class="subtitle">Siti Cookies offers a wide selection of delicious cookies, easy to order online, and exciting latest promotions and discounts for loyal customers!</p>
            <a href="Shop.php"><button type="button" class="BuyNow">Buy Now</button></a>
        </div>

        <img class="banner" src="assets/images/Banner.png" alt="Banner">

        <h2>Top 3 Best Seller</h2>
        <p class="subtitle">Here are the three best-selling cookies at Siti Cookies</p>
        <div class="products">
            <div class="product">
                <img src="assets/images/AlmondLondon.jpg" alt="Almond London">
                <h3>Almond London</h3>
                <p>Almond, chocolate-coated</p>
                <p style="font-weight:bold;color:#333;">RM30.00</p>
            </div>
            <div class="product">
                <img src="assets/images/TartNenas.jpg" alt="Tart Nenas">
                <h3>Tart Nenas</h3>
                <p>Pineapple jam tart</p>
                <p style="font-weight:bold;color:#333;">RM28.00</p>
            </div>
            <div class="product">
                <img src="assets/images/NutellaChocolate.jpg" alt="Nutella Chocolate">
                <h3>Nutella Chocolate</h3>
                <p>Nutella-filled chocolate</p>
                <p style="font-weight:bold;color:#333;">RM30.00</p>
            </div>
        </div>

        <h2>Comments</h2>
        <div class="comments">
            <?php if ($comments !== []): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p class="user"><?= h($comment['name']); ?></p>
                        <p class="meta"><?= h($comment['created_at'] ?? ''); ?></p>
                        <p class="message"><?= nl2br(h($comment['message'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="comment">
                    <p class="message">No comments yet. Be the first to leave one.</p>
                </div>
            <?php endif; ?>
        </div>

        <h2>Leave a Comment!</h2>
        <p class="subtitle">If you have any questions or feedback, we'd love to hear from you!</p>
        <div class="leave-comment">
            <form method="POST">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <textarea name="message" placeholder="Message" rows="5" required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <?php render_site_footer('FAQ.php'); ?>
    <?php render_logout_script(); ?>
</body>
</html>
