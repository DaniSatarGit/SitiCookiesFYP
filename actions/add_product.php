<?php
include '../config/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name_product = $_POST['name_product'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $short_desc = $_POST['short_desc'];

    // Handle file upload
    $target_dir = "../assets/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image = basename($_FILES["image"]["name"]);

        $sql = "INSERT INTO product (name_product, image, quantity, price, short_desc) VALUES ('$name_product', '$image', $quantity, '$price', '$short_desc')";

        if ($conn->query($sql) === TRUE) {
            echo "New product added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }

    $conn->close();
}

// Add this at the end of the file
header("Location: ../AdminHome.php?success=add");
exit();

?>

