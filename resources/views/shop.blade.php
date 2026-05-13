@extends('layouts.site', ['active' => 'products'])

@section('title', 'Siti Cookies Products')

@section('content')
    <main class="container">
        <section class="center">
            <h1>Cookies Price 2024</h1>
        </section>

        <div class="grid products-grid">
            @forelse ($products as $product)
                <article class="product shop-product">
                    <img src="{{ asset('assets/images/'.basename($product->image)) }}" alt="{{ $product->name_product }}">
                    <div class="product-heading">
                        <h3>{{ $product->name_product }}</h3>
                        <p class="product-card-price">RM{{ number_format((float) $product->price, 2) }}</p>
                    </div>
                    <p class="muted">{{ $product->short_desc }}</p>
                    <p class="muted">Stock available: {{ (int) $product->quantity }}</p>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ (int) $product->id }}">
                        <input type="number" name="quantity" min="1" max="{{ max(1, (int) $product->quantity) }}" value="1" {{ (int) $product->quantity === 0 ? 'disabled' : '' }}>
                        <button type="submit" {{ (int) $product->quantity === 0 ? 'disabled' : '' }}>
                            {{ (int) $product->quantity === 0 ? 'Out of Stock' : 'Add to Cart' }}
                        </button>
                    </form>
                </article>
            @empty
                <p>No products available.</p>
            @endforelse
        </div>
    </main>
@endsection
