@extends('layouts.site', ['active' => 'home'])

@section('title', 'Siti Cookies Shop')

@section('content')
    <main class="container">
        <section class="center">
            <h1>SITI COOKIES SHOP</h1>
            <p class="subtitle">Siti Cookies offers a wide selection of delicious cookies, easy to order online, and exciting latest promotions and discounts for loyal customers!</p>
            <a href="{{ route('shop') }}" class="btn">Buy Now</a>
        </section>

        <img class="banner" src="{{ asset('assets/images/Banner.png') }}" alt="Banner">

        <h2>Top 3 Best Seller</h2>
        <p class="subtitle">Here are the three best-selling cookies at Siti Cookies</p>
        <div class="grid three-grid">
            @foreach ([
                ['image' => 'AlmondLondon.jpg', 'name' => 'Almond London', 'desc' => 'Almond, chocolate-coated', 'price' => 'RM30.00'],
                ['image' => 'TartNenas.jpg', 'name' => 'Tart Nenas', 'desc' => 'Pineapple jam tart', 'price' => 'RM28.00'],
                ['image' => 'NutellaChocolate.jpg', 'name' => 'Nutella Chocolate', 'desc' => 'Nutella-filled chocolate', 'price' => 'RM30.00'],
            ] as $item)
                <article class="product">
                    <img src="{{ asset('assets/images/'.$item['image']) }}" alt="{{ $item['name'] }}">
                    <h3>{{ $item['name'] }}</h3>
                    <p class="muted">{{ $item['desc'] }}</p>
                    <p><strong>{{ $item['price'] }}</strong></p>
                </article>
            @endforeach
        </div>

        <h2>Comments</h2>
        <section class="card">
            @forelse ($comments as $comment)
                <article class="comment">
                    <p><strong>{{ $comment->name }}</strong></p>
                    <p class="muted">{{ $comment->created_at }}</p>
                    <p>{!! nl2br(e($comment->message)) !!}</p>
                </article>
            @empty
                <article class="comment">
                    <p class="muted">No comments yet. Be the first to leave one.</p>
                </article>
            @endforelse
        </section>

        <h2>Leave a Comment!</h2>
        <p class="subtitle">If you have any questions or feedback, we'd love to hear from you!</p>
        <form class="grid" method="POST" action="{{ route('home') }}">
            @csrf
            <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
            <textarea name="message" placeholder="Message" rows="5" required>{{ old('message') }}</textarea>
            <button type="submit" style="width:160px;">Submit</button>
        </form>
    </main>
@endsection
