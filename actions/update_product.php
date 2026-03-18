<?php
require_once __DIR__ . '/../includes/bootstrap.php';

require_admin('../LoginSignup.php');

function save_product_image_if_present(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
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
    $targetPath = __DIR__ . '/../assets/images/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $nameProduct = trim($_POST['name_product'] ?? '');
    $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
    $price = (float) ($_POST['price'] ?? 0);
    $shortDesc = trim($_POST['short_desc'] ?? '');

    if ($productId <= 0 || $nameProduct === '' || $shortDesc === '' || $price < 0) {
        set_flash('error', 'Please provide valid product details.');
        redirect('../AdminHome.php');
    }

    try {
        $image = save_product_image_if_present($_FILES['image'] ?? []);
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
        redirect('../AdminHome.php');
    }

    if ($image !== null) {
        $stmt = $conn->prepare('UPDATE product SET name_product = ?, image = ?, quantity = ?, price = ?, short_desc = ? WHERE id = ?');
        $stmt->bind_param('ssidsi', $nameProduct, $image, $quantity, $price, $shortDesc, $productId);
    } else {
        $stmt = $conn->prepare('UPDATE product SET name_product = ?, quantity = ?, price = ?, short_desc = ? WHERE id = ?');
        $stmt->bind_param('sidsi', $nameProduct, $quantity, $price, $shortDesc, $productId);
    }

    if ($stmt->execute()) {
        set_flash('success', 'Product updated successfully.');
    } else {
        set_flash('error', 'Unable to update product.');
    }

    $stmt->close();
}

redirect('../AdminHome.php');
