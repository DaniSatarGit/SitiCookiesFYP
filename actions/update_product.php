<?php
include '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $name_product = $_POST['name_product'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $short_desc = $_POST['short_desc'];

    // Handle file upload if a new file is provided
    if ($_FILES["image"]["name"]) {
        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = basename($_FILES["image"]["name"]);
            $sql = "UPDATE product SET name_product='$name_product', image='$image', quantity=$quantity, price='$price', short_desc='$short_desc' WHERE id=$product_id";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        $sql = "UPDATE product SET name_product='$name_product', quantity=$quantity, price='$price', short_desc='$short_desc' WHERE id=$product_id";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Product updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}

// Add this at the end of the file
header("Location: ../AdminHome.php?success=update");
exit();

?>

