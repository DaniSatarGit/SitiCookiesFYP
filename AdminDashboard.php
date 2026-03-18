<?php
// Set the time zone to Malaysia Time (MYT)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Start the session
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include __DIR__ . '/config/db_connection.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the 'orders' table with associated items
$sql = "SELECT o.order_id, o.username, o.address, o.state, o.postcode, o.city, o.time_order, o.payment_method, o.receipt, o.total, o.status,
               GROUP_CONCAT(oi.product ORDER BY oi.product SEPARATOR ', ') as products, 
               GROUP_CONCAT(oi.quantity ORDER BY oi.product SEPARATOR ', ') as quantities
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id";
$result = $conn->query($sql);

if (!$result) {
    die("Error in query: " . $conn->error);
}

// Calculate total sales and number of orders for the current month
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');

$sqlMonth = "SELECT SUM(o.total) as totalSales, COUNT(o.order_id) as totalOrders 
             FROM orders o 
             WHERE o.time_order BETWEEN '$startOfMonth' AND '$endOfMonth'";
$resultMonth = $conn->query($sqlMonth);

if (!$resultMonth) {
    die("Error in query: " . $conn->error);
}

$monthData = $resultMonth->fetch_assoc();
$totalSalesMonth = $monthData['totalSales'] ?? 0;
$totalOrdersMonth = $monthData['totalOrders'] ?? 0;

// Calculate total sales and number of orders for the current week
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$sqlWeek = "SELECT SUM(o.total) as totalSales, COUNT(o.order_id) as totalOrders 
            FROM orders o 
            WHERE o.time_order BETWEEN '$startOfWeek' AND '$endOfWeek'";
$resultWeek = $conn->query($sqlWeek);

if (!$resultWeek) {
    die("Error in query: " . $conn->error);
}

$weekData = $resultWeek->fetch_assoc();
$totalSalesWeek = $weekData['totalSales'] ?? 0;
$totalOrdersWeek = $weekData['totalOrders'] ?? 0;

$sqlMonthlySales = "SELECT DATE_FORMAT(time_order, '%Y-%m') AS month, SUM(total) AS total_sales 
                    FROM orders 
                    GROUP BY month 
                    ORDER BY month";
$resultMonthlySales = $conn->query($sqlMonthlySales);

$monthlySalesData = [];
while ($row = $resultMonthlySales->fetch_assoc()) {
    $monthlySalesData[] = $row;
}

$sqlPaymentMethods = "SELECT payment_method, COUNT(order_id) AS method_count 
                      FROM orders 
                      GROUP BY payment_method";
$resultPaymentMethods = $conn->query($sqlPaymentMethods);

$paymentMethodsData = [];
while ($row = $resultPaymentMethods->fetch_assoc()) {
    $paymentMethodsData[] = $row;
}

$sqlOrdersByState = "SELECT state, COUNT(order_id) AS order_count 
                     FROM orders 
                     GROUP BY state";
$resultOrdersByState = $conn->query($sqlOrdersByState);

$ordersByStateData = [];
while ($row = $resultOrdersByState->fetch_assoc()) {
    $ordersByStateData[] = $row;
}

$sqlAverageOrderValue = "SELECT DATE_FORMAT(time_order, '%Y-%m') AS month, AVG(total) AS avg_order_value 
                         FROM orders 
                         GROUP BY month 
                         ORDER BY month";
$resultAverageOrderValue = $conn->query($sqlAverageOrderValue);

$averageOrderValueData = [];
while ($row = $resultAverageOrderValue->fetch_assoc()) {
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
        /* Existing CSS styles */
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
            padding: 10px;
        }
        
        .admin-container h1 {
            margin-bottom: 20px;
        }
        
        .dashboard {
            width: 100%;
            max-width: 1200px;
        }
        
        .dashboard p {
            text-align: left;
            margin-bottom: 20px;
        }
        
        .dashboard .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        
        .dashboard .stats .stat {
            background-color: #fff;
            padding: 14px;
            border-radius: 7px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 30%;
            text-align: center;
        }
        
        .dashboard .recent-orders,
        .dashboard .recent-month {
            background-color: #fff;
            padding: 20px;
            border-radius: 7px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: block;
            overflow-x: auto;
        }
        
        .dashboard table {
            width: 100%;
            border-collapse: collapse;
            padding : 100px;
        }
        
        .dashboard table th,
        .dashboard table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
            border: 1px #E0E0E0;
            border-bottom-style: solid;
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
        
        /* Chart Grid Styles */
        .chart-grid {
            display: flex;
            flex-wrap: wrap; /* Allows the containers to wrap to the next line */
            gap: 20px; /* Space between containers */
            justify-content: center; /* Center the grid horizontally */
            max-width: 1200px; /* Adjust width as needed */
            margin: 8px auto;
            
        }
        
        /* Chart Container Styles */
        .chart-container {
            flex: 1 1 calc(50% - 20px); /* 50% width minus the gap */
            min-width: 300px; /* Adjust the minimum width as needed */
            background-color: #fff;
            border-radius: 7px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            box-sizing: border-box;
        }
        
        /* Canvas Element Styles */
        canvas {
            width: 100%;
            height: auto; /* Maintain aspect ratio */
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
                font-size: 12px;
            }
        
            .dashboard .stats {
                flex-direction: column;
                align-items: center;
            }
        
            .dashboard .stats .stat {
                width: 80%;
                margin-bottom: 20px;
            }
        
            .chart-grid {
                flex-direction: column;
            }
        
            .chart-container {
                width: 100%;
                min-width: auto;
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
        
            .admin-container h1 {
                font-size: 20px;
            }
        
            .dashboard .stats .stat {
                width: 100%;
                margin-bottom: 10px;
            }
        
            .chart-container {
                flex: 1 1 100%;
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
                <li><a href="AdminHome.php">Products</a></li>
                <li><a href="AdminOrder.php">Order</a></li>
                <li><a href="AdminDashboard.php" style="color:#C80000">Dashboard</a></li>
                <li><a href="AdminComment.php">Comment</a></li>
                <li><a href="#" class="login-signup" id="logoutBtn">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="admin-container">
            <h1>Admin Dashboard</h1>
            <div class="dashboard">
                <div class="stats">
                    <div class="stat">
                        <p style="font-weight: bold; font-size: 15px; margin: auto">Total Sales</p>
                        <p style="font-weight: bold; font-size: 35px; margin: auto">RM<?php echo number_format($totalSalesMonth, 2); ?></p>
                        <p style="font-size: 13px; color: grey; margin: auto">This month</p>
                    </div>
                    <div class="stat">
                        <p style="font-weight: bold; font-size: 15px; margin: auto">Total Sales</p>
                        <p style="font-weight: bold; font-size: 35px; margin: auto">RM<?php echo number_format($totalSalesWeek, 2); ?></p>
                        <p style="font-size: 13px; color: grey; margin: auto">This week</p>
                    </div>
                    <div class="stat">
                        <p style="font-weight: bold; font-size: 15px; margin: auto">Number of Orders</p>
                        <p style="font-weight: bold; font-size: 35px; margin: auto"><?php echo $totalOrdersMonth; ?></p>
                        <p style="font-size: 13px; color: grey; margin: auto">This month</p>
                    </div>
                </div>
                <div class="recent-orders">
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
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['time_order']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                        <td><?php echo htmlspecialchars($row['products']); ?></td>
                                        <td><?php echo htmlspecialchars($row['quantities']); ?></td>
                                        <td>RM<?php echo htmlspecialchars($row['total']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="chart-grid">
            <div class="chart-container">
                <canvas id="monthlySalesChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="ordersByStateChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="aovChart"></canvas>
            </div>
        </div>
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
    </main>
    <footer>
        <div class="footer-content">
            <img src="assets/images/Logo.png" alt="Siti Cookies">
            <span>Copyright @2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a>
                <br>
                <a href="AdminFAQ.php"><span style="font-weight: 900; margin-left: 0px; font-size: 15px">FAQ</span></a>
            </div>
        </div>
    </footer>
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
        
        const monthlySalesLabels = <?php echo json_encode(array_column($monthlySalesData, 'month')); ?>;
        const monthlySalesValues = <?php echo json_encode(array_column($monthlySalesData, 'total_sales')); ?>;
        
        const ctxMonthlySales = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(ctxMonthlySales, {
            type: 'line',
            data: {
                labels: monthlySalesLabels,
                datasets: [{
                    label: 'Monthly Sales Growth',
                    data: monthlySalesValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    fill: false,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        const paymentMethodLabels = <?php echo json_encode(array_column($paymentMethodsData, 'payment_method')); ?>;
        const paymentMethodValues = <?php echo json_encode(array_column($paymentMethodsData, 'method_count')); ?>;
        
        const ctxPaymentMethods = document.getElementById('paymentMethodsChart').getContext('2d');
        const paymentMethodsChart = new Chart(ctxPaymentMethods, {
            type: 'pie',
            data: {
                labels: paymentMethodLabels,
                datasets: [{
                    data: paymentMethodValues,
                    backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 206, 86, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
        
        const stateLabels = <?php echo json_encode(array_column($ordersByStateData, 'state')); ?>;
        const stateOrderValues = <?php echo json_encode(array_column($ordersByStateData, 'order_count')); ?>;
        
        const ctxOrdersByState = document.getElementById('ordersByStateChart').getContext('2d');
        const ordersByStateChart = new Chart(ctxOrdersByState, {
            type: 'bar',
            data: {
                labels: stateLabels,
                datasets: [{
                    label: 'Orders by State',
                    data: stateOrderValues,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        const aovLabels = <?php echo json_encode(array_column($averageOrderValueData, 'month')); ?>;
        const aovValues = <?php echo json_encode(array_column($averageOrderValueData, 'avg_order_value')); ?>;
        
        const ctxAOV = document.getElementById('aovChart').getContext('2d');
        const aovChart = new Chart(ctxAOV, {
            type: 'bar',
            data: {
                labels: aovLabels,
                datasets: [{
                    label: 'Average Order Value',
                    data: aovValues,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
