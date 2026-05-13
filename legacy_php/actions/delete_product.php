<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin('../LoginSignup.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        set_flash('error', 'Invalid product selected.');
        redirect('../AdminHome.php');
    }

    $stmt = $conn->prepare('DELETE FROM product WHERE id = ?');
    $stmt->bind_param('i', $productId);

    if ($stmt->execute()) {
        set_flash('success', 'Product deleted successfully.');
    } else {
        set_flash('error', 'Unable to delete product.');
    }

    $stmt->close();
}

redirect('../AdminHome.php');
