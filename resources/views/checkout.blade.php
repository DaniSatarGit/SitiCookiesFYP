@extends('layouts.site', ['active' => 'cart'])

@section('title', 'Siti Cookies Checkout')

@section('content')
    <main class="container checkout-layout">
        <section class="panel">
            <h2>Check Out</h2>
            @if ($cartItems !== [])
                <form id="checkout-form" method="POST" action="{{ route('checkout.place') }}" enctype="multipart/form-data">
                    @csrf
                    @foreach ($cartItems as $index => $item)
                        <div class="product-item" data-product-id="{{ (int) $item['id'] }}" data-price="{{ number_format((float) $item['price'], 2, '.', '') }}">
                            <input type="checkbox" class="item-select" checked>
                            <img src="{{ asset('assets/images/'.basename($item['image'])) }}" alt="{{ $item['name_product'] }}">
                            <div>
                                <div><strong>{{ $item['name_product'] }}</strong></div>
                                <div class="muted">{{ $item['short_desc'] }}</div>
                                <div class="muted">Stock available: {{ (int) $item['stock'] }}</div>
                            </div>
                            <div class="product-price">RM{{ number_format((float) $item['price'], 2) }}</div>
                            <div class="quantity-input">
                                <button type="button" onclick="changeQuantity({{ $index }}, -1)">-</button>
                                <input type="number" class="quantity-field" value="{{ (int) $item['quantity'] }}" min="1" max="{{ (int) $item['stock'] }}">
                                <button type="button" onclick="changeQuantity({{ $index }}, 1)">+</button>
                                <a class="btn btn-danger remove-button" href="{{ route('checkout', ['remove_id' => (int) $item['id']]) }}">Remove</a>
                            </div>
                        </div>
                    @endforeach

                    <div class="address-grid">
                        <input type="text" name="address" placeholder="Address" value="{{ old('address') }}" required>
                        <input type="text" name="state" placeholder="State" value="{{ old('state') }}" required>
                        <input type="text" name="postcode" placeholder="Postcode" value="{{ old('postcode') }}" required>
                        <input type="text" name="city" placeholder="City" value="{{ old('city') }}" required>
                    </div>

                    <div class="payment-method">
                        <label><input type="radio" name="payment" value="Cash On Delivery" checked> Cash on Delivery</label>
                        <label><input type="radio" name="payment" value="Online Banking"> Online Banking</label>
                    </div>

                    <div class="instructions" id="banking-instructions">
                        <h3>Online Banking Payment Instructions</h3>
                        <p>Transfer to Maybank account 123456789 under Siti Cookies Shop, then upload your receipt below.</p>
                        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <input type="hidden" name="cart_state" id="cart-state">
                </form>
            @else
                <p>Your cart is empty. Add some cookies from the products page first.</p>
            @endif
        </section>

        <aside class="panel">
            <h3>Order Summary</h3>
            <div class="summary-row"><span>Subtotal</span><span id="subtotal">RM0.00</span></div>
            <div class="summary-row"><span>Shipping</span><span id="shipping">RM0.00</span></div>
            <div class="summary-row"><span>Tax</span><span id="tax">RM0.00</span></div>
            <div class="summary-row"><strong>Total</strong><strong id="total">RM0.00</strong></div>
            @if ($cartItems !== [])
                <button type="button" onclick="submitCheckout()">Place Order</button>
            @endif
        </aside>
    </main>
@endsection

@push('scripts')
    <script>
        const shippingFee = 4.90;
        const taxFee = 1.00;

        function getItems() {
            return Array.from(document.querySelectorAll('.product-item'));
        }

        function changeQuantity(index, delta) {
            const item = getItems()[index];
            const input = item.querySelector('.quantity-field');
            const min = parseInt(input.min, 10);
            const max = parseInt(input.max, 10);
            const next = Math.max(min, Math.min(max, parseInt(input.value, 10) + delta));
            input.value = next;
            updateOrderSummary();
        }

        function buildCartState() {
            return getItems().map((item) => ({
                product_id: parseInt(item.dataset.productId, 10),
                quantity: Math.max(1, parseInt(item.querySelector('.quantity-field').value, 10) || 1),
                selected: item.querySelector('.item-select').checked,
            }));
        }

        function updateOrderSummary() {
            let subtotal = 0;

            getItems().forEach((item) => {
                const checked = item.querySelector('.item-select').checked;
                const price = parseFloat(item.dataset.price);
                const quantity = Math.max(1, parseInt(item.querySelector('.quantity-field').value, 10) || 1);
                item.querySelector('.quantity-field').value = quantity;

                if (checked) {
                    subtotal += price * quantity;
                }
            });

            const hasSelectedItems = subtotal > 0;
            const shipping = hasSelectedItems ? shippingFee : 0;
            const tax = hasSelectedItems ? taxFee : 0;
            const total = subtotal + shipping + tax;

            document.getElementById('subtotal').innerText = 'RM' + subtotal.toFixed(2);
            document.getElementById('shipping').innerText = 'RM' + shipping.toFixed(2);
            document.getElementById('tax').innerText = 'RM' + tax.toFixed(2);
            document.getElementById('total').innerText = 'RM' + total.toFixed(2);
            document.getElementById('cart-state').value = JSON.stringify(buildCartState());
        }

        function toggleInstructions() {
            const onlineBanking = document.querySelector('input[name="payment"][value="Online Banking"]').checked;
            document.getElementById('banking-instructions').style.display = onlineBanking ? 'block' : 'none';
        }

        function submitCheckout() {
            const selectedItem = buildCartState().some((item) => item.selected);
            if (!selectedItem) {
                alert('Please select at least one item.');
                return;
            }

            updateOrderSummary();
            document.getElementById('checkout-form').submit();
        }

        document.querySelectorAll('.item-select, .quantity-field').forEach((element) => {
            element.addEventListener('change', updateOrderSummary);
        });

        document.querySelectorAll('input[name="payment"]').forEach((element) => {
            element.addEventListener('change', toggleInstructions);
        });

        if (document.getElementById('checkout-form')) {
            toggleInstructions();
            updateOrderSummary();
        }
    </script>
@endpush
