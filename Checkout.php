<?php
require_once __DIR__ . '/includes/store.php';
require_once __DIR__ . '/includes/layout.php';

if (isset($_GET['remove_id'])) {
    remove_from_cart((int) $_GET['remove_id']);
    set_flash('success', 'Item removed from cart.');
    redirect('Checkout.php');
}

$cartItems = refresh_cart_products($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    require_login('LoginSignup.php');

    $address = trim($_POST['address'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $paymentMethod = trim($_POST['payment'] ?? '');
    $allowedPayments = ['Cash On Delivery', 'Online Banking'];

    if ($address === '' || $state === '' || $postcode === '' || $city === '') {
        set_flash('error', 'Please fill in all address fields.');
        redirect('Checkout.php');
    }

    if (!in_array($paymentMethod, $allowedPayments, true)) {
        set_flash('error', 'Please choose a valid payment method.');
        redirect('Checkout.php');
    }

    $selection = build_checkout_selection($conn, parse_cart_state($_POST['cart_state'] ?? null));
    $orderItems = $selection['items'];

    if ($orderItems === []) {
        set_flash('error', 'Please select at least one item to place an order.');
        redirect('Checkout.php');
    }

    $subtotal = $selection['subtotal'];
    $shipping = $selection['shipping'];
    $tax = $selection['tax'];
    $total = $subtotal + $shipping + $tax;
    $receipt = '';

    if ($paymentMethod === 'Online Banking') {
        try {
            $receipt = store_receipt_upload($_FILES['receipt'] ?? []);
        } catch (RuntimeException $exception) {
            set_flash('error', $exception->getMessage());
            redirect('Checkout.php');
        }
    }

    $conn->begin_transaction();

    try {
        $timeOrder = date('Y-m-d H:i:s');
        $username = (string) current_user();
        $status = 'Pending';

        $orderStmt = $conn->prepare('INSERT INTO orders (username, address, state, postcode, city, time_order, payment_method, receipt, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $orderStmt->bind_param('ssssssssds', $username, $address, $state, $postcode, $city, $timeOrder, $paymentMethod, $receipt, $total, $status);

        if (!$orderStmt->execute()) {
            throw new RuntimeException('Unable to save the order.');
        }

        $orderId = $conn->insert_id;
        $orderStmt->close();

        $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product, quantity) VALUES (?, ?, ?)');
        $stockStmt = $conn->prepare('UPDATE product SET quantity = quantity - ? WHERE id = ? AND quantity >= ?');

        foreach ($orderItems as $item) {
            $productName = $item['name_product'];
            $quantity = (int) $item['quantity'];
            $productId = (int) $item['id'];

            $itemStmt->bind_param('isi', $orderId, $productName, $quantity);
            if (!$itemStmt->execute()) {
                throw new RuntimeException('Unable to save order items.');
            }

            $stockStmt->bind_param('iii', $quantity, $productId, $quantity);
            $stockStmt->execute();
            if ($stockStmt->affected_rows !== 1) {
                throw new RuntimeException('One or more products no longer have enough stock.');
            }
        }

        $itemStmt->close();
        $stockStmt->close();
        $conn->commit();

        $cartState = parse_cart_state($_POST['cart_state'] ?? null);
        $remainingCart = [];
        foreach (refresh_cart_products($conn) as $item) {
            $productId = (int) $item['id'];
            if (($cartState[$productId]['selected'] ?? true) === false) {
                $item['quantity'] = min((int) $item['quantity'], (int) $item['stock']);
                $remainingCart[] = $item;
            }
        }
        set_cart($remainingCart);

        set_flash('success', 'Order placed successfully.');
        redirect('OrderSuccess.php');
    } catch (RuntimeException $exception) {
        $conn->rollback();
        set_flash('error', $exception->getMessage());
        redirect('Checkout.php');
    }
}

$cartItems = refresh_cart_products($conn);
?>
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
        .continue-btn,
        .quantity-button,
        .remove-button,
        .submit-button,
        button {
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .login-signup {
            background-color: #000;
            color: #fff;
            padding: 11px 25px;
            font-size: 10px;
        }

        .checkout-layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px 50px;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
            gap: 24px;
        }

        .panel {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 20px;
        }

        .product-item {
            display: grid;
            grid-template-columns: 24px 72px 1fr auto auto;
            gap: 12px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-item img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-description {
            color: #7a7a7a;
            font-size: 13px;
            margin-top: 6px;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-button,
        .remove-button,
        .submit-button,
        .continue-btn,
        button {
            background-color: #000;
            color: #fff;
            padding: 10px 14px;
        }

        .quantity-input input,
        .address-grid input,
        .payment-method label,
        .upload-group input {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
        }

        .quantity-input input {
            width: 64px;
            text-align: center;
        }

        .remove-button {
            background-color: #9F2D2D;
            text-decoration: none;
            display: inline-block;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .address-grid input {
            width: 100%;
            box-sizing: border-box;
        }

        .payment-method {
            margin-top: 20px;
            display: grid;
            gap: 10px;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #F1E8DA;
        }

        .instructions {
            display: none;
            margin-top: 18px;
            background-color: #FCF7F0;
            border-radius: 10px;
            padding: 16px;
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

        @media (max-width: 900px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            header nav ul li {
                margin: 0 5px;
            }

            .product-item {
                grid-template-columns: 24px 60px 1fr;
            }

            .quantity-input,
            .product-price,
            .remove-button {
                grid-column: 3;
                justify-self: start;
            }

            .address-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php render_site_header('cart'); ?>
    <?php render_flash(); ?>

    <main class="checkout-layout">
        <section class="panel">
            <h2>Check Out</h2>
            <?php if ($cartItems !== []): ?>
                <form id="checkout-form" method="POST" enctype="multipart/form-data">
                    <?php foreach ($cartItems as $index => $item): ?>
                        <div class="product-item" data-product-id="<?= (int) $item['id']; ?>" data-price="<?= number_format((float) $item['price'], 2, '.', ''); ?>">
                            <input type="checkbox" class="item-select" checked>
                            <img src="assets/images/<?= h(basename($item['image'])); ?>" alt="<?= h($item['name_product']); ?>">
                            <div>
                                <div><strong><?= h($item['name_product']); ?></strong></div>
                                <div class="product-description"><?= h($item['short_desc']); ?></div>
                                <div class="product-description">Stock available: <?= (int) $item['stock']; ?></div>
                            </div>
                            <div class="product-price">RM<?= number_format((float) $item['price'], 2); ?></div>
                            <div class="quantity-input">
                                <button type="button" class="quantity-button" onclick="changeQuantity(<?= $index; ?>, -1)">-</button>
                                <input type="number" class="quantity-field" value="<?= (int) $item['quantity']; ?>" min="1" max="<?= (int) $item['stock']; ?>">
                                <button type="button" class="quantity-button" onclick="changeQuantity(<?= $index; ?>, 1)">+</button>
                                <a class="remove-button" href="Checkout.php?remove_id=<?= (int) $item['id']; ?>">Remove</a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="address-grid">
                        <input type="text" name="address" placeholder="Address" required>
                        <input type="text" name="state" placeholder="State" required>
                        <input type="text" name="postcode" placeholder="Postcode" required>
                        <input type="text" name="city" placeholder="City" required>
                    </div>

                    <div class="payment-method">
                        <label><input type="radio" name="payment" value="Cash On Delivery" checked> Cash on Delivery</label>
                        <label><input type="radio" name="payment" value="Online Banking"> Online Banking</label>
                    </div>

                    <div class="instructions" id="banking-instructions">
                        <h3>Online Banking Payment Instructions</h3>
                        <p>Transfer to Maybank account 123456789 under Siti Cookies Shop, then upload your receipt below.</p>
                        <div class="upload-group">
                            <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>

                    <input type="hidden" name="cart_state" id="cart-state">
                    <input type="hidden" name="place_order" value="1">
                </form>
            <?php else: ?>
                <p>Your cart is empty. Add some cookies from the products page first.</p>
            <?php endif; ?>
        </section>

        <aside class="panel">
            <h3>Order Summary</h3>
            <div class="summary-row"><span>Subtotal</span><span id="subtotal">RM0.00</span></div>
            <div class="summary-row"><span>Shipping</span><span id="shipping">RM0.00</span></div>
            <div class="summary-row"><span>Tax</span><span id="tax">RM0.00</span></div>
            <div class="summary-row"><strong>Total</strong><strong id="total">RM0.00</strong></div>
            <?php if ($cartItems !== []): ?>
                <button type="button" class="continue-btn" onclick="submitCheckout()">Place Order</button>
            <?php endif; ?>
        </aside>
    </main>

    <?php render_site_footer('FAQ.php'); ?>
    <?php render_logout_script(); ?>

    <script>
        const shippingFee = 4.90;
        const taxFee = 1.00;

        function getItems() {
            return Array.from(document.querySelectorAll('.product-item'));
        }

        function changeQuantity(index, delta) {
            const item = getItems()[index];
            const input = item.querySelector('.quantity-field');
            const min = parseInt(input.min, 10);
            const max = parseInt(input.max, 10);
            const next = Math.max(min, Math.min(max, parseInt(input.value, 10) + delta));
            input.value = next;
            updateOrderSummary();
        }

        function updateOrderSummary() {
            let subtotal = 0;

            getItems().forEach((item) => {
                const checked = item.querySelector('.item-select').checked;
                const price = parseFloat(item.dataset.price);
                const quantity = Math.max(1, parseInt(item.querySelector('.quantity-field').value, 10) || 1);
                item.querySelector('.quantity-field').value = quantity;

                if (checked) {
                    subtotal += price * quantity;
                }
            });

            const hasSelectedItems = subtotal > 0;
            const shipping = hasSelectedItems ? shippingFee : 0;
            const tax = hasSelectedItems ? taxFee : 0;
            const total = subtotal + shipping + tax;

            document.getElementById('subtotal').innerText = 'RM' + subtotal.toFixed(2);
            document.getElementById('shipping').innerText = 'RM' + shipping.toFixed(2);
            document.getElementById('tax').innerText = 'RM' + tax.toFixed(2);
            document.getElementById('total').innerText = 'RM' + total.toFixed(2);
            document.getElementById('cart-state').value = JSON.stringify(buildCartState());
        }

        function buildCartState() {
            return getItems().map((item) => ({
                product_id: parseInt(item.dataset.productId, 10),
                quantity: Math.max(1, parseInt(item.querySelector('.quantity-field').value, 10) || 1),
                selected: item.querySelector('.item-select').checked,
            }));
        }

        function toggleInstructions() {
            const onlineBanking = document.querySelector('input[name="payment"][value="Online Banking"]').checked;
            document.getElementById('banking-instructions').style.display = onlineBanking ? 'block' : 'none';
        }

        function submitCheckout() {
            const selectedItem = buildCartState().some((item) => item.selected);
            if (!selectedItem) {
                alert('Please select at least one item.');
                return;
            }

            updateOrderSummary();
            document.getElementById('checkout-form').submit();
        }

        document.querySelectorAll('.item-select, .quantity-field').forEach((element) => {
            element.addEventListener('change', updateOrderSummary);
        });

        document.querySelectorAll('input[name="payment"]').forEach((element) => {
            element.addEventListener('change', toggleInstructions);
        });

        toggleInstructions();
        updateOrderSummary();
    </script>
</body>
</html>
