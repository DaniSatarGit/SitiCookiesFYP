<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin('../LoginSignup.php');

function save_product_image(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please upload a product image.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Product image must be a JPG or PNG file.');
    }

    $extension = $allowedMimeTypes[$mimeType];
    $filename = 'product_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetDir = __DIR__ . '/../assets/images';
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nameProduct = trim($_POST['name_product'] ?? '');
    $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
    $price = (float) ($_POST['price'] ?? 0);
    $shortDesc = trim($_POST['short_desc'] ?? '');

    if ($nameProduct === '' || $shortDesc === '' || $price < 0) {
        set_flash('error', 'Please provide valid product details.');
        redirect('../AdminHome.php');
    }

    try {
        $image = save_product_image($_FILES['image'] ?? []);
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
        redirect('../AdminHome.php');
    }

    $stmt = $conn->prepare('INSERT INTO product (name_product, image, quantity, price, short_desc) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssids', $nameProduct, $image, $quantity, $price, $shortDesc);

    if ($stmt->execute()) {
        set_flash('success', 'Product added successfully.');
    } else {
        set_flash('error', 'Unable to add product.');
    }

    $stmt->close();
}

redirect('../AdminHome.php');
