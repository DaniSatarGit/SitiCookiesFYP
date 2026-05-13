<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/bootstrap.php';

require_admin();

$sql = "SELECT o.order_id, o.username, o.time_order, o.payment_method, o.total, o.status,
               GROUP_CONCAT(oi.product ORDER BY oi.product SEPARATOR ', ') AS products,
               GROUP_CONCAT(oi.quantity ORDER BY oi.product SEPARATOR ', ') AS quantities
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id
        ORDER BY o.time_order DESC
        LIMIT 10";
$result = $conn->query($sql);
$recentOrders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$startOfMonth = date('Y-m-01 00:00:00');
$endOfMonth = date('Y-m-t 23:59:59');
$startOfWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
$endOfWeek = date('Y-m-d 23:59:59', strtotime('sunday this week'));

$stmt = $conn->prepare('SELECT COALESCE(SUM(total), 0), COUNT(order_id) FROM orders WHERE time_order BETWEEN ? AND ?');
$stmt->bind_param('ss', $startOfMonth, $endOfMonth);
$stmt->execute();
$stmt->bind_result($totalSalesMonth, $totalOrdersMonth);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COALESCE(SUM(total), 0), COUNT(order_id) FROM orders WHERE time_order BETWEEN ? AND ?');
$stmt->bind_param('ss', $startOfWeek, $endOfWeek);
$stmt->execute();
$stmt->bind_result($totalSalesWeek, $totalOrdersWeek);
$stmt->fetch();
$stmt->close();

$monthlySalesData = [];
$result = $conn->query("SELECT DATE_FORMAT(time_order, '%Y-%m') AS month, SUM(total) AS total_sales FROM orders GROUP BY month ORDER BY month");
while ($result && $row = $result->fetch_assoc()) {
    $monthlySalesData[] = $row;
}

$paymentMethodsData = [];
$result = $conn->query('SELECT payment_method, COUNT(order_id) AS method_count FROM orders GROUP BY payment_method');
while ($result && $row = $result->fetch_assoc()) {
    $paymentMethodsData[] = $row;
}

$ordersByStateData = [];
$result = $conn->query('SELECT state, COUNT(order_id) AS order_count FROM orders GROUP BY state');
while ($result && $row = $result->fetch_assoc()) {
    $ordersByStateData[] = $row;
}

$averageOrderValueData = [];
$result = $conn->query("SELECT DATE_FORMAT(time_order, '%Y-%m') AS month, AVG(total) AS avg_order_value FROM orders GROUP BY month ORDER BY month");
while ($result && $row = $result->fetch_assoc()) {
    $averageOrderValueData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Admin Dashboard - Siti Cookies</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        main { max-width: 1280px; margin: 0 auto; padding: 24px 20px 50px; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat, .table-card, .chart-container { background: #fff; border-radius: 14px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); padding: 20px; }
        .stat p { margin: 0; }
        .stat .value { font-size: 32px; font-weight: 700; margin: 8px 0; }
        .table-card { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .chart-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin-top: 24px; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 450px; text-align: center; border-radius: 10px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        footer .footer-content span { font-weight: 200; font-style: italic; color: #828282; font-size: 12px; }
        footer .social-icons img { height: 30px; }
        @media (max-width: 960px) { .stats, .chart-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { header nav ul { flex-wrap: wrap; justify-content: center; } header nav ul li { margin: 0 5px; } }
    </style>
</head>
<body>
    <?php render_admin_header('dashboard'); ?>
    <?php render_flash(); ?>

    <main>
        <h1>Admin Dashboard</h1>

        <div class="stats">
            <div class="stat"><p>Total Sales</p><p class="value">RM<?= number_format((float) $totalSalesMonth, 2); ?></p><p>This month</p></div>
            <div class="stat"><p>Total Sales</p><p class="value">RM<?= number_format((float) $totalSalesWeek, 2); ?></p><p>This week</p></div>
            <div class="stat"><p>Orders</p><p class="value"><?= (int) $totalOrdersMonth; ?></p><p>This month</p></div>
            <div class="stat"><p>Orders</p><p class="value"><?= (int) $totalOrdersWeek; ?></p><p>This week</p></div>
        </div>

        <div class="table-card">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Time Order</th>
                        <th>Payment Method</th>
                        <th>Products</th>
                        <th>Quantities</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders !== []): ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= h($order['username']); ?></td>
                                <td><?= h($order['time_order']); ?></td>
                                <td><?= h($order['payment_method']); ?></td>
                                <td><?= h($order['products']); ?></td>
                                <td><?= h($order['quantities']); ?></td>
                                <td>RM<?= number_format((float) $order['total'], 2); ?></td>
                                <td><?= h($order['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="chart-grid">
            <div class="chart-container"><canvas id="monthlySalesChart"></canvas></div>
            <div class="chart-container"><canvas id="paymentMethodsChart"></canvas></div>
            <div class="chart-container"><canvas id="ordersByStateChart"></canvas></div>
            <div class="chart-container"><canvas id="aovChart"></canvas></div>
        </div>
    </main>

    <?php render_site_footer('AdminFAQ.php'); ?>
    <?php render_logout_script('actions/logout.php'); ?>

    <script>
        const monthlySalesLabels = <?= json_encode(array_column($monthlySalesData, 'month')); ?>;
        const monthlySalesValues = <?= json_encode(array_map('floatval', array_column($monthlySalesData, 'total_sales'))); ?>;
        const paymentMethodLabels = <?= json_encode(array_column($paymentMethodsData, 'payment_method')); ?>;
        const paymentMethodValues = <?= json_encode(array_map('intval', array_column($paymentMethodsData, 'method_count'))); ?>;
        const stateLabels = <?= json_encode(array_column($ordersByStateData, 'state')); ?>;
        const stateOrderValues = <?= json_encode(array_map('intval', array_column($ordersByStateData, 'order_count'))); ?>;
        const aovLabels = <?= json_encode(array_column($averageOrderValueData, 'month')); ?>;
        const aovValues = <?= json_encode(array_map('floatval', array_column($averageOrderValueData, 'avg_order_value'))); ?>;

        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: { labels: monthlySalesLabels, datasets: [{ label: 'Monthly Sales Growth', data: monthlySalesValues, borderColor: 'rgba(54, 162, 235, 1)', backgroundColor: 'rgba(54, 162, 235, 0.2)', fill: false }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('paymentMethodsChart'), {
            type: 'pie',
            data: { labels: paymentMethodLabels, datasets: [{ data: paymentMethodValues, backgroundColor: ['rgba(75, 192, 192, 0.3)', 'rgba(255, 99, 132, 0.3)', 'rgba(255, 206, 86, 0.3)'] }] },
            options: { responsive: true }
        });

        new Chart(document.getElementById('ordersByStateChart'), {
            type: 'bar',
            data: { labels: stateLabels, datasets: [{ label: 'Orders by State', data: stateOrderValues, backgroundColor: 'rgba(153, 102, 255, 0.3)', borderColor: 'rgba(153, 102, 255, 1)', borderWidth: 1 }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('aovChart'), {
            type: 'bar',
            data: { labels: aovLabels, datasets: [{ label: 'Average Order Value', data: aovValues, backgroundColor: 'rgba(255, 159, 64, 0.3)', borderColor: 'rgba(255, 159, 64, 1)', borderWidth: 1 }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
