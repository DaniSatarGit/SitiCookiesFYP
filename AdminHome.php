<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/bootstrap.php';

require_admin();

$result = $conn->query('SELECT id, name_product, quantity, price FROM product ORDER BY name_product');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Admin Home - Siti Cookies</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; background-color: #FCF7F0; color: #333; }
        header, footer { background-color: #F1E8DA; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        header .logo img, footer .footer-content img { height: 40px; margin-left: 25px; }
        header nav ul, footer .footer-content { display: flex; align-items: center; justify-content: space-between; margin: 0; padding: 0; }
        header nav ul { list-style: none; }
        header nav ul li { margin-left: 40px; }
        header nav ul li a { text-decoration: none; color: #333; font-weight: 600; font-size: 14px; }
        .login-signup { background-color: #000; color: #fff; padding: 11px 25px; border-radius: 5px; font-size: 10px; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 30px 20px 50px; }
        .admin-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
        .admin-card, .product-table { background: #fff; border-radius: 14px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); padding: 20px; }
        .admin-card form { display: flex; flex-direction: column; gap: 12px; }
        .admin-card input, .admin-card select, .admin-card button { padding: 12px; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; }
        .admin-card button { background-color: #000; color: #fff; border: none; cursor: pointer; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .dropdown { position: relative; display: inline-block; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 450px; text-align: center; border-radius: 10px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        footer .footer-content span { font-weight: 200; font-style: italic; color: #828282; font-size: 12px; }
        footer .social-icons img { height: 30px; }
        @media (max-width: 960px) { .admin-actions { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { header nav ul { flex-wrap: wrap; justify-content: center; } header nav ul li { margin: 0 5px; } }
    </style>
</head>
<body>
    <?php render_admin_header('products'); ?>
    <?php render_flash(); ?>

    <main class="admin-container">
        <h1>Admin Home</h1>

        <div class="admin-actions">
            <section class="admin-card">
                <h2>Add Product</h2>
                <form action="actions/add_product.php" method="POST" enctype="multipart/form-data">
                    <input type="text" name="name_product" placeholder="Product Name" required>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png" required>
                    <input type="number" name="quantity" placeholder="Stock Quantity" min="0" required>
                    <input type="number" step="0.01" min="0" name="price" placeholder="Price (0.00)" required>
                    <input type="text" name="short_desc" placeholder="Short Description" required>
                    <button type="submit">Add Product</button>
                </form>
            </section>

            <section class="admin-card">
                <h2>Edit Product</h2>
                <form action="actions/update_product.php" method="POST" enctype="multipart/form-data">
                    <select name="product_id" required>
                        <option value="" disabled selected>Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id']; ?>"><?= h($product['name_product']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="name_product" placeholder="Product Name" required>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png">
                    <input type="number" name="quantity" placeholder="Stock Quantity" min="0" required>
                    <input type="number" step="0.01" min="0" name="price" placeholder="Price (0.00)" required>
                    <input type="text" name="short_desc" placeholder="Short Description" required>
                    <button type="submit">Update Product</button>
                </form>
            </section>

            <section class="admin-card">
                <h2>Remove Product</h2>
                <form action="actions/delete_product.php" method="POST">
                    <select name="product_id" required>
                        <option value="" disabled selected>Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int) $product['id']; ?>"><?= h($product['name_product']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Remove Product</button>
                </form>
            </section>
        </div>

        <section class="product-table" style="margin-top:24px;">
            <h2>Current Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Stock</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= (int) $product['id']; ?></td>
                            <td><?= h($product['name_product']); ?></td>
                            <td><?= (int) $product['quantity']; ?></td>
                            <td>RM<?= number_format((float) $product['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php render_site_footer('AdminFAQ.php'); ?>
    <?php render_logout_script('actions/logout.php'); ?>
</body>
</html>
