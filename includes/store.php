<?php
require_once __DIR__ . '/bootstrap.php';

function get_cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function set_cart(array $cart): void
{
    $_SESSION['cart'] = array_values($cart);
}

function refresh_cart_products(mysqli $conn): array
{
    $cart = get_cart();

    if ($cart === []) {
        return [];
    }

    $updatedCart = [];
    $stmt = $conn->prepare('SELECT id, name_product, image, price, short_desc, quantity FROM product WHERE id = ?');

    foreach ($cart as $item) {
        $productId = (int) ($item['id'] ?? 0);

        if ($productId <= 0) {
            continue;
        }

        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            continue;
        }

        $requestedQuantity = max(1, (int) ($item['quantity'] ?? 1));
        $availableQuantity = max(0, (int) $product['quantity']);

        if ($availableQuantity === 0) {
            continue;
        }

        $updatedCart[] = [
            'id' => (int) $product['id'],
            'name_product' => $product['name_product'],
            'image' => $product['image'],
            'price' => (float) $product['price'],
            'short_desc' => $product['short_desc'],
            'quantity' => min($requestedQuantity, $availableQuantity),
            'stock' => $availableQuantity,
        ];
    }

    $stmt->close();
    set_cart($updatedCart);

    return $updatedCart;
}

function add_product_to_cart(mysqli $conn, int $productId, int $quantity = 1): bool
{
    if ($productId <= 0 || $quantity <= 0) {
        return false;
    }

    $stmt = $conn->prepare('SELECT id, name_product, image, price, short_desc, quantity FROM product WHERE id = ?');
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product || (int) $product['quantity'] <= 0) {
        return false;
    }

    $cart = get_cart();

    foreach ($cart as &$item) {
        if ((int) $item['id'] === $productId) {
            $item['quantity'] = min((int) $product['quantity'], (int) $item['quantity'] + $quantity);
            set_cart($cart);

            return true;
        }
    }
    unset($item);

    $cart[] = [
        'id' => (int) $product['id'],
        'name_product' => $product['name_product'],
        'image' => $product['image'],
        'price' => (float) $product['price'],
        'short_desc' => $product['short_desc'],
        'quantity' => min($quantity, (int) $product['quantity']),
        'stock' => (int) $product['quantity'],
    ];

    set_cart($cart);

    return true;
}

function remove_from_cart(int $productId): void
{
    $cart = array_filter(
        get_cart(),
        static fn(array $item): bool => (int) ($item['id'] ?? 0) !== $productId
    );

    set_cart($cart);
}

function parse_cart_state(?string $rawState): array
{
    if ($rawState === null || trim($rawState) === '') {
        return [];
    }

    $decoded = json_decode($rawState, true);

    if (!is_array($decoded)) {
        return [];
    }

    $normalized = [];

    foreach ($decoded as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $productId = (int) ($entry['product_id'] ?? 0);
        $quantity = max(1, (int) ($entry['quantity'] ?? 1));
        $selected = !empty($entry['selected']);

        if ($productId > 0) {
            $normalized[$productId] = [
                'quantity' => $quantity,
                'selected' => $selected,
            ];
        }
    }

    return $normalized;
}

function build_checkout_selection(mysqli $conn, array $cartState): array
{
    $cart = refresh_cart_products($conn);
    $lineItems = [];
    $subtotal = 0.0;

    foreach ($cart as $item) {
        $productId = (int) $item['id'];
        $requested = $cartState[$productId] ?? [
            'quantity' => (int) $item['quantity'],
            'selected' => true,
        ];

        if (!$requested['selected']) {
            continue;
        }

        $quantity = min((int) $item['stock'], max(1, (int) $requested['quantity']));
        $lineTotal = (float) $item['price'] * $quantity;

        $lineItems[] = [
            'id' => $productId,
            'name_product' => $item['name_product'],
            'price' => (float) $item['price'],
            'quantity' => $quantity,
            'line_total' => $lineTotal,
            'stock' => (int) $item['stock'],
        ];

        $subtotal += $lineTotal;
    }

    return [
        'items' => $lineItems,
        'subtotal' => $subtotal,
        'shipping' => $lineItems === [] ? 0.0 : 4.90,
        'tax' => $lineItems === [] ? 0.0 : 1.00,
    ];
}

function store_receipt_upload(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Receipt upload failed.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Receipt must be a JPG, PNG, or PDF file.');
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Receipt file is too large. Maximum size is 5MB.');
    }

    $extension = $allowedMimeTypes[$mimeType];
    $uploadDir = __DIR__ . '/../assets/uploads';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Unable to prepare upload directory.');
    }

    $filename = 'receipt_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Unable to save uploaded receipt.');
    }

    return 'assets/uploads/' . $filename;
}

function submit_comment(mysqli $conn, string $name, string $email, string $message): bool
{
    $stmt = $conn->prepare('INSERT INTO comment (name, email, message) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $message);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function fetch_comments(mysqli $conn, int $limit = 6): array
{
    $comments = [];
    $limit = max(1, $limit);
    $query = 'SELECT name, email, message, created_at FROM comment ORDER BY created_at DESC LIMIT ' . $limit;
    $result = $conn->query($query);

    if (!$result) {
        return [];
    }

    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    return $comments;
}
