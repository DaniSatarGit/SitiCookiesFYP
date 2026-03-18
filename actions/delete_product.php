<?php
include '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];

    $sql = "DELETE FROM product WHERE id=$product_id";

    if ($conn->query($sql) === TRUE) {
        echo "Product deleted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}

// Add this at the end of the file
header("Location: ../AdminHome.php?success=delete");
exit();

?>

