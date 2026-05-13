<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
    <title>Order Success - Siti Cookies</title>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: #fff;
        }

        .success-box {
            max-width: 760px;
            padding: 30px;
            background: #fcf7f0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .success-box img {
            width: 100px;
        }
    </style>
    <script>
        setTimeout(function () {
            window.location.href = '{{ route('home') }}';
        }, 5000);
    </script>
</head>
<body>
    <main class="success-box">
        <h1>Order Placed Successfully!</h1>
        <img src="{{ asset('assets/images/accept.png') }}" alt="Order Success">
        <p>Thank you for your order! We are thrilled to have the opportunity to serve you.</p>
        <p>Your order has been received and is currently being processed. Please allow 1-2 business days for order processing.</p>
        <a href="{{ route('home') }}" class="btn" style="background:#f1e8da;color:#000;">Continue Shopping</a>
        <p class="muted">This page will redirect to the home page in 5 seconds.</p>
    </main>
</body>
</html>
