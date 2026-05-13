<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
    <title>@yield('title', 'Siti Cookies Shop')</title>
    @stack('head')
</head>
<body>
    @php
        $isAdminLayout = $adminLayout ?? false;
        $activeKey = $active ?? '';
        $siteLinks = [
            'home' => ['label' => 'Home', 'href' => route('home')],
            'products' => ['label' => 'Products', 'href' => route('shop')],
            'cart' => ['label' => 'Cart', 'href' => route('checkout')],
        ];
        $adminLinks = [
            'products' => ['label' => 'Products', 'href' => route('admin.products')],
            'orders' => ['label' => 'Order', 'href' => route('admin.orders')],
            'dashboard' => ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
            'comments' => ['label' => 'Comment', 'href' => route('admin.comments')],
        ];
        $links = $isAdminLayout ? $adminLinks : $siteLinks;
        $homeLink = $isAdminLayout ? route('admin.products') : route('home');
    @endphp

    <header>
        <div class="logo">
            <a href="{{ $homeLink }}"><img src="{{ asset('assets/images/Logo.png') }}" alt="Siti Cookies"></a>
        </div>
        <nav>
            <ul>
                @foreach ($links as $key => $link)
                    <li><a class="{{ $activeKey === $key ? 'active' : '' }}" href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                @endforeach

                @if ($isAdminLayout)
                    <li><a href="javascript:void(0)" class="login-signup" onclick="confirmLogout()">Logout</a></li>
                @elseif (session()->has('username'))
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="login-signup">{{ session('username') }}</a>
                        <div class="dropdown-content">
                            <a href="{{ route('profile') }}">Profile</a>
                            <a href="javascript:void(0)" onclick="confirmLogout()">Logout</a>
                        </div>
                    </li>
                @else
                    <li><a class="{{ request()->routeIs('signup') ? 'active' : '' }}" href="{{ route('signup') }}">Sign Up</a></li>
                    <li><a href="{{ route('login') }}" class="login-signup">Login</a></li>
                @endif
            </ul>
        </nav>
        <div id="logoutModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Are you sure you want to logout?</h2>
                <button type="button" onclick="logout()">Yes</button>
                <button type="button" onclick="closeModal()">No</button>
            </div>
        </div>
    </header>

    @foreach (['success', 'error'] as $flashType)
        @if (session($flashType))
            <div class="flash {{ $flashType }}">{{ session($flashType) }}</div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="flash error">{{ $errors->first() }}</div>
    @endif

    @yield('content')

    <footer>
        <div class="footer-content">
            <img src="{{ asset('assets/images/Logo.png') }}" alt="Siti Cookies">
            <span>Copyright @2024 Siti Cookies</span>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/QxLx6VcdovGtKxer/?mibextid=LQQJ4d"><img src="{{ asset('assets/images/facebook.png') }}" alt="Facebook"></a>
                <a href="https://www.instagram.com/sitizaleha9278?igsh=MXFiNWI2ZHFsOThyZw=="><img src="{{ asset('assets/images/instagram.png') }}" alt="Instagram"></a>
                <br>
                <a href="{{ $isAdminLayout ? route('admin.faq') : route('faq') }}"><span style="font-weight:900;font-size:15px;">FAQ</span></a>
            </div>
        </div>
    </footer>

    <script>
        function confirmLogout() {
            document.getElementById('logoutModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function logout() {
            window.location.href = '{{ route('logout') }}';
        }

        window.addEventListener('click', function (event) {
            const modal = document.getElementById('logoutModal');
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
