@extends('layouts.site', ['adminLayout' => true, 'active' => 'products'])

@section('title', 'Admin Home - Siti Cookies')

@section('content')
    <main class="container">
        <h1>Admin Home</h1>
        <div class="grid three-grid">
            <section class="card">
                <h2>Add Product</h2>
                <form class="grid" action="{{ route('admin.products.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="text" name="name_product" placeholder="Product Name" required>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png" required>
                    <input type="number" name="quantity" placeholder="Stock Quantity" min="0" required>
                    <input type="number" step="0.01" min="0" name="price" placeholder="Price (0.00)" required>
                    <input type="text" name="short_desc" placeholder="Short Description" required>
                    <button type="submit">Add Product</button>
                </form>
            </section>

            <section class="card">
                <h2>Edit Product</h2>
                <form class="grid" action="{{ route('admin.products.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <select name="product_id" required>
                        <option value="" disabled selected>Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ (int) $product->id }}">{{ $product->name_product }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="name_product" placeholder="Product Name" required>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png">
                    <input type="number" name="quantity" placeholder="Stock Quantity" min="0" required>
                    <input type="number" step="0.01" min="0" name="price" placeholder="Price (0.00)" required>
                    <input type="text" name="short_desc" placeholder="Short Description" required>
                    <button type="submit">Update Product</button>
                </form>
            </section>

            <section class="card">
                <h2>Remove Product</h2>
                <form class="grid" action="{{ route('admin.products.delete') }}" method="POST">
                    @csrf
                    <select name="product_id" required>
                        <option value="" disabled selected>Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ (int) $product->id }}">{{ $product->name_product }}</option>
                        @endforeach
                    </select>
                    <button type="submit">Remove Product</button>
                </form>
            </section>
        </div>

        <section class="table-card" style="margin-top:14px;">
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
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ (int) $product->id }}</td>
                            <td>{{ $product->name_product }}</td>
                            <td>{{ (int) $product->quantity }}</td>
                            <td>RM{{ number_format((float) $product->price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
@endsection
