<?php
session_start();
include __DIR__ . '/config/db_connection.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $order_id);

    if ($stmt->execute()) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $stmt->close();
    header('Location: AdminOrder.php');
    exit;
}

// Fetch data from the 'orders' table with associated items
$sql = "SELECT o.order_id, o.username, o.address, o.state, o.postcode, o.city, o.time_order, o.payment_method, o.receipt, o.total, o.status,
               GROUP_CONCAT(oi.product ORDER BY oi.product SEPARATOR ', ') as products,
               GROUP_CONCAT(oi.quantity ORDER BY oi.product SEPARATOR ', ') as quantities
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Admin Orders - Siti Cookies</title>
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

        main {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            font-size: 12px;
            display: block;
            overflow-x: auto;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #F9F9F9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        .radio-group {
            display: flex;
            gap: 10px;
        }

        .radio-group input {
            margin-right: 5px;
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

        .modal-header, .modal-footer {
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
                font-size: 10px; /* Smaller font size for mobile */
            }
        }
        
        @media (max-width: 480px) {
            header nav ul li {
                margin-left: 5px; /* Further adjust spacing */
                margin-right: 5px; /* Add right margin for spacing */
            }
        
            header nav ul li a {
                font-size: 8px; /* Ensure font size is small */
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
            <a href="AdminHome.php"><img src="assets/images/Logo.png" alt="Siti Cookies"></a>
        </div>
        <nav>
            <ul>
                <li><a href="AdminHome.php">Products</a></li>
                <li><a href="AdminOrder.php" style="color:#C80000">Order</a></li>
                <li><a href="AdminDashboard.php">Dashboard</a></li>
                <li><a href="AdminComment.php">Comment</a></li>
                <li><a href="#" class="login-signup" id="logoutBtn">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <table>
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
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['order_id'] . "</td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['address'] . "</td>";
                    echo "<td>" . $row['state'] . "</td>";
                    echo "<td>" . $row['postcode'] . "</td>";
                    echo "<td>" . $row['city'] . "</td>";
                    echo "<td>" . $row['time_order'] . "</td>";
                    echo "<td>" . $row['payment_method'] . "</td>";
                    echo "<td>" . $row['receipt'] . "</td>";
                    echo "<td>RM " . number_format($row['total'], 2) . "</td>";
                    echo "<td>" . $row['products'] . "</td>";
                    echo "<td>" . $row['quantities'] . "</td>";
                    echo "<td>
                            <form method='post' action=''>
                                <div class='radio-group'>
                                    <input type='radio' name='status' value='Pending' " . ($row['status'] == 'Pending' ? 'checked' : '') . "> Pending
                                    <input type='radio' name='status' value='Shipping' " . ($row['status'] == 'Shipping' ? 'checked' : '') . "> Shipping
                                    <input type='radio' name='status' value='Complete' " . ($row['status'] == 'Complete' ? 'checked' : '') . "> Complete
                                </div>
                                <input type='hidden' name='order_id' value='" . $row['order_id'] . "'>
                                <input type='submit' value='Update'>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='13'>No orders found</td></tr>";
            }
            ?>
        </table>
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
