<?php
require_once __DIR__ . '/bootstrap.php';

function render_flash(): void
{
    $flash = get_flash();

    if ($flash === null) {
        return;
    }

    $background = $flash['type'] === 'error' ? '#FDE8E8' : '#E7F7EE';
    $border = $flash['type'] === 'error' ? '#E36D6D' : '#4A9B6E';

    echo '<div style="max-width:1100px;margin:20px auto 0;padding:12px 16px;border-radius:10px;border:1px solid ' . h($border) . ';background-color:' . h($background) . ';color:#333;">' . h($flash['message']) . '</div>';
}

function render_site_header(string $active = ''): void
{
    $links = [
        'home' => ['label' => 'Home', 'href' => 'index.php'],
        'products' => ['label' => 'Products', 'href' => 'Shop.php'],
        'cart' => ['label' => 'Cart', 'href' => 'Checkout.php'],
    ];

    echo '<header>';
    echo '<div class="logo"><a href="index.php"><img src="assets/images/Logo.png" alt="Siti Cookies"></a></div>';
    echo '<nav><ul>';

    foreach ($links as $key => $link) {
        $style = $active === $key ? ' style="color:#C80000"' : '';
        echo '<li><a href="' . h($link['href']) . '"' . $style . '>' . h($link['label']) . '</a></li>';
    }

    if (is_logged_in()) {
        echo '<li class="dropdown">';
        echo '<a href="javascript:void(0)" class="login-signup">' . h((string) current_user()) . '</a>';
        echo '<div class="dropdown-content">';
        echo '<a href="Profile.php">Profile</a>';
        echo '<a href="javascript:void(0)" onclick="confirmLogout()">Logout</a>';
        echo '</div></li>';
    } else {
        echo '<li><a href="LoginSignup.php" class="login-signup">Login / SignUp</a></li>';
    }

    echo '</ul></nav>';
    echo '<div id="logoutModal" class="modal"><div class="modal-content"><span class="close" onclick="closeModal()">&times;</span><h2>Are you sure you want to logout?</h2><button type="button" onclick="logout()">Yes</button><button type="button" onclick="closeModal()">No</button></div></div>';
    echo '</header>';
}

function render_admin_header(string $active = ''): void
{
    $links = [
        'products' => ['label' => 'Products', 'href' => 'AdminHome.php'],
        'orders' => ['label' => 'Order', 'href' => 'AdminOrder.php'],
        'dashboard' => ['label' => 'Dashboard', 'href' => 'AdminDashboard.php'],
        'comments' => ['label' => 'Comment', 'href' => 'AdminComment.php'],
    ];

    echo '<header>';
    echo '<div class="logo"><a href="AdminHome.php"><img src="assets/images/Logo.png" alt="Siti Cookies"></a></div>';
    echo '<nav><ul>';

    foreach ($links as $key => $link) {
        $style = $active === $key ? ' style="color:#C80000"' : '';
        echo '<li><a href="' . h($link['href']) . '"' . $style . '>' . h($link['label']) . '</a></li>';
    }

    echo '<li><a href="javascript:void(0)" class="login-signup" onclick="confirmLogout()">Logout</a></li>';
    echo '</ul></nav>';
    echo '<div id="logoutModal" class="modal"><div class="modal-content"><span class="close" onclick="closeModal()">&times;</span><h2>Are you sure you want to logout?</h2><button type="button" onclick="logout()">Yes</button><button type="button" onclick="closeModal()">No</button></div></div>';
    echo '</header>';
}

function render_site_footer(string $faqPath = 'FAQ.php'): void
{
    echo '<footer><div class="footer-content">';
    echo '<img src="assets/images/Logo.png" alt="Siti Cookies">';
    echo '<span>Copyright @2024 Siti Cookies</span>';
    echo '<div class="social-icons">';
    echo '<a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="assets/images/facebook.png" alt="Facebook"></a>';
    echo '<a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="assets/images/instagram.png" alt="Instagram"></a><br>';
    echo '<a href="' . h($faqPath) . '"><span style="font-weight:900;font-size:15px;">FAQ</span></a>';
    echo '</div></div></footer>';
}

function render_logout_script(string $logoutPath = 'actions/logout.php'): void
{
    echo '<script>
        function confirmLogout() {
            document.getElementById("logoutModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        function logout() {
            window.location.href = "' . h($logoutPath) . '";
        }

        window.addEventListener("click", function (event) {
            const modal = document.getElementById("logoutModal");
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>';
}
