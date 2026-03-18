<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/store.php';

require_admin();
$comments = fetch_comments($conn, 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <title>Admin Comment</title>
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
        .container { max-width: 1100px; margin: 0 auto; padding: 24px 20px 50px; }
        .comment { background: #fff; padding: 16px; margin: 12px 0; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .user { font-weight: 700; }
        .timestamp { color: #828282; font-size: 0.85em; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 450px; text-align: center; border-radius: 10px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        footer .footer-content span { font-weight: 200; font-style: italic; color: #828282; font-size: 12px; }
        footer .social-icons img { height: 30px; }
        @media (max-width: 768px) { header nav ul { flex-wrap: wrap; justify-content: center; } header nav ul li { margin: 0 5px; } }
    </style>
</head>
<body>
    <?php render_admin_header('comments'); ?>
    <?php render_flash(); ?>

    <div class="container">
        <h1>Admin Comment</h1>
        <?php if ($comments !== []): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p class="user"><?= h($comment['name']); ?></p>
                    <p class="timestamp"><?= h($comment['created_at'] ?? ''); ?></p>
                    <p><strong>Email:</strong> <?= h($comment['email']); ?></p>
                    <p><?= nl2br(h($comment['message'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments available.</p>
        <?php endif; ?>
    </div>

    <?php render_site_footer('AdminFAQ.php'); ?>
    <?php render_logout_script('actions/logout.php'); ?>
</body>
</html>
