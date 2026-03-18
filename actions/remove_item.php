<?php
session_start();

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $productId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Redirect back to checkout page
    header("Location: ../Checkout.php");
    exit();
}
?>

