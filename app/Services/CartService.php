<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCart(): array
    {
        return session('cart', []);
    }

    public function setCart(array $cart): void
    {
        session(['cart' => array_values($cart)]);
    }

    public function refreshProducts(): array
    {
        $cart = $this->getCart();

        if ($cart === []) {
            return [];
        }

        $ids = collect($cart)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            $this->setCart([]);

            return [];
        }

        $products = DB::table('product')
            ->select('id', 'name_product', 'image', 'price', 'short_desc', 'quantity')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $updatedCart = [];

        foreach ($cart as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $requestedQuantity = max(1, (int) ($item['quantity'] ?? 1));
            $availableQuantity = max(0, (int) $product->quantity);

            if ($availableQuantity === 0) {
                continue;
            }

            $updatedCart[] = [
                'id' => (int) $product->id,
                'name_product' => $product->name_product,
                'image' => $product->image,
                'price' => (float) $product->price,
                'short_desc' => $product->short_desc,
                'quantity' => min($requestedQuantity, $availableQuantity),
                'stock' => $availableQuantity,
            ];
        }

        $this->setCart($updatedCart);

        return $updatedCart;
    }

    public function addProduct(int $productId, int $quantity = 1): bool
    {
        if ($productId <= 0 || $quantity <= 0) {
            return false;
        }

        $product = DB::table('product')
            ->select('id', 'name_product', 'image', 'price', 'short_desc', 'quantity')
            ->where('id', $productId)
            ->first();

        if (! $product || (int) $product->quantity <= 0) {
            return false;
        }

        $cart = $this->getCart();

        foreach ($cart as &$item) {
            if ((int) $item['id'] === $productId) {
                $item['quantity'] = min((int) $product->quantity, (int) $item['quantity'] + $quantity);
                $this->setCart($cart);

                return true;
            }
        }
        unset($item);

        $cart[] = [
            'id' => (int) $product->id,
            'name_product' => $product->name_product,
            'image' => $product->image,
            'price' => (float) $product->price,
            'short_desc' => $product->short_desc,
            'quantity' => min($quantity, (int) $product->quantity),
            'stock' => (int) $product->quantity,
        ];

        $this->setCart($cart);

        return true;
    }

    public function remove(int $productId): void
    {
        $cart = array_filter(
            $this->getCart(),
            static fn (array $item): bool => (int) ($item['id'] ?? 0) !== $productId
        );

        $this->setCart($cart);
    }

    public function parseCartState(?string $rawState): array
    {
        if ($rawState === null || trim($rawState) === '') {
            return [];
        }

        $decoded = json_decode($rawState, true);

        if (! is_array($decoded)) {
            return [];
        }

        $normalized = [];

        foreach ($decoded as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $productId = (int) ($entry['product_id'] ?? 0);
            $quantity = max(1, (int) ($entry['quantity'] ?? 1));
            $selected = ! empty($entry['selected']);

            if ($productId > 0) {
                $normalized[$productId] = [
                    'quantity' => $quantity,
                    'selected' => $selected,
                ];
            }
        }

        return $normalized;
    }

    public function buildCheckoutSelection(array $cartState): array
    {
        $cart = $this->refreshProducts();
        $lineItems = [];
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $productId = (int) $item['id'];
            $requested = $cartState[$productId] ?? [
                'quantity' => (int) $item['quantity'],
                'selected' => true,
            ];

            if (! $requested['selected']) {
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

    public function selectedRemainingCart(array $cartState): array
    {
        $remainingCart = [];

        foreach ($this->refreshProducts() as $item) {
            $productId = (int) $item['id'];

            if (($cartState[$productId]['selected'] ?? true) === false) {
                $item['quantity'] = min((int) $item['quantity'], (int) $item['stock']);
                $remainingCart[] = $item;
            }
        }

        return $remainingCart;
    }
}
