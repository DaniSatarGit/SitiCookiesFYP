<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $allowedStatuses = ['Pending', 'Shipping', 'Complete'];

    if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
        set_flash('error', 'Invalid order update request.');
        redirect('AdminOrder.php');
    }

    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
    $stmt->bind_param('si', $status, $orderId);

    if ($stmt->execute()) {
        set_flash('success', 'Order status updated successfully.');
    } else {
        set_flash('error', 'Unable to update order status.');
    }

    $stmt->close();
    redirect('AdminOrder.php');
}

$sql = "SELECT o.order_id, o.username, o.address, o.state, o.postcode, o.city, o.time_order, o.payment_method, o.receipt, o.total, o.status,
               GROUP_CONCAT(oi.product ORDER BY oi.product SEPARATOR ', ') AS products,
               GROUP_CONCAT(oi.quantity ORDER BY oi.product SEPARATOR ', ') AS quantities
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id
        ORDER BY o.time_order DESC";
$result = $conn->query($sql);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Admin Orders - Siti Cookies</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; background-color: #FCF7F0; color: #333; }
        header, footer { background-color: #F1E8DA; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        header .logo img, footer .footer-content img { height: 40px; margin-left: 25px; }
        header nav ul, footer .footer-content { display: flex; align-items: center; justify-content: space-between; margin: 0; padding: 0; }
        header nav ul { list-style: none; }
        header nav ul li { margin-left: 40px; }
        header nav ul li a { text-decoration: none; color: #333; font-weight: 600; font-size: 14px; }
        .login-signup { background-color: #000; color: #fff; padding: 11px 25px; border-radius: 5px; font-size: 10px; }
        main { max-width: 1400px; margin: 0 auto; padding: 24px 20px 50px; }
        .table-card { background: #fff; border-radius: 14px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); padding: 20px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: top; text-align: left; }
        select, button { padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
        button { background-color: #000; color: #fff; border: none; cursor: pointer; margin-top: 8px; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 450px; text-align: center; border-radius: 10px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        footer .footer-content span { font-weight: 200; font-style: italic; color: #828282; font-size: 12px; }
        footer .social-icons img { height: 30px; }
        @media (max-width: 768px) { header nav ul { flex-wrap: wrap; justify-content: center; } header nav ul li { margin: 0 5px; } }
    </style>
</head>
<body>
    <?php render_admin_header('orders'); ?>
    <?php render_flash(); ?>

    <main>
        <div class="table-card">
            <h1>Admin Orders</h1>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Username</th>
                        <th>Address</th>
                        <th>State</th>
                        <th>Postcode</th>
                        <th>City</th>
                        <th>Time Order</th>
                        <th>Payment Method</th>
                        <th>Receipt</th>
                        <th>Total</th>
                        <th>Products</th>
                        <th>Quantities</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders !== []): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= (int) $order['order_id']; ?></td>
                                <td><?= h($order['username']); ?></td>
                                <td><?= h($order['address']); ?></td>
                                <td><?= h($order['state']); ?></td>
                                <td><?= h($order['postcode']); ?></td>
                                <td><?= h($order['city']); ?></td>
                                <td><?= h($order['time_order']); ?></td>
                                <td><?= h($order['payment_method']); ?></td>
                                <td>
                                    <?php if (!empty($order['receipt'])): ?>
                                        <a href="<?= h($order['receipt']); ?>" target="_blank" rel="noopener noreferrer">View receipt</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>RM<?= number_format((float) $order['total'], 2); ?></td>
                                <td><?= h($order['products']); ?></td>
                                <td><?= h($order['quantities']); ?></td>
                                <td>
                                    <form method="post" action="AdminOrder.php">
                                        <select name="status">
                                            <?php foreach (['Pending', 'Shipping', 'Complete'] as $status): ?>
                                                <option value="<?= h($status); ?>" <?= $order['status'] === $status ? 'selected' : ''; ?>><?= h($status); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="order_id" value="<?= (int) $order['order_id']; ?>">
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="13">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php render_site_footer('AdminFAQ.php'); ?>
    <?php render_logout_script('actions/logout.php'); ?>
</body>
</html>
