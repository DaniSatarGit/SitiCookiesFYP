<?php
require_once __DIR__ . '/includes/store.php';
require_once __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in before adding items to your cart.');
        redirect('LoginSignup.php');
    }

    $productId = (int) $_POST['product_id'];
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    if (add_product_to_cart($conn, $productId, $quantity)) {
        set_flash('success', 'Product added to cart.');
    } else {
        set_flash('error', 'Unable to add this product to cart.');
    }

    redirect('Shop.php');
}

$result = $conn->query('SELECT id, name_product, image, price, short_desc, quantity FROM product ORDER BY name_product');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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

        .login-signup {
            background-color: #000;
            color: #fff;
            padding: 11px 25px;
            border-radius: 5px;
            font-size: 10px;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 20px;
        }

        .content {
            text-align: center;
            padding: 20px;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .product {
            background-color: #F1E8DA;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        .product img {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product p {
            color: #828282;
            font-size: 13px;
        }

        .product-price {
            color: #333;
            font-size: 15px;
            font-weight: bold;
        }

        .stock {
            font-size: 12px;
            color: #666;
        }

        .product form {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 10px;
            margin-top: auto;
            align-items: center;
        }

        .product input,
        .product button {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #d4c7b6;
        }

        .product button {
            background-color: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 700;
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

        footer .footer-content span {
            font-weight: 200;
            font-style: italic;
            color: #828282;
            font-size: 12px;
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
        }
    </style>
</head>
<body>
    <?php render_site_header('products'); ?>
    <?php render_flash(); ?>

    <div class="container">
        <div class="content">
            <h1>Cookies Price 2024</h1>
        </div>

        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="assets/images/<?= h(basename($product['image'])); ?>" alt="<?= h($product['name_product']); ?>">
                    <h3><?= h($product['name_product']); ?></h3>
                    <p><?= h($product['short_desc']); ?></p>
                    <p class="product-price">RM<?= number_format((float) $product['price'], 2); ?></p>
                    <p class="stock">Stock available: <?= (int) $product['quantity']; ?></p>
                    <form action="Shop.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id']; ?>">
                        <input type="number" name="quantity" min="1" max="<?= max(1, (int) $product['quantity']); ?>" value="1" <?= (int) $product['quantity'] === 0 ? 'disabled' : ''; ?>>
                        <button type="submit" <?= (int) $product['quantity'] === 0 ? 'disabled' : ''; ?>><?= (int) $product['quantity'] === 0 ? 'Out of Stock' : 'Add to Cart'; ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php render_site_footer('FAQ.php'); ?>
    <?php render_logout_script(); ?>
</body>
</html>
