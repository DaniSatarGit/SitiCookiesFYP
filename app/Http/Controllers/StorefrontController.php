<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class StorefrontController extends Controller
{
    public function home(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            return $this->submitComment($request);
        }

        $comments = DB::table('comment')
            ->select('name', 'email', 'message', 'created_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('home', compact('comments'));
    }

    public function submitComment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        DB::table('comment')->insert($data);

        return redirect()->route('home')->with('success', 'Comment submitted successfully.');
    }

    public function shop(): View
    {
        $products = DB::table('product')
            ->select('id', 'name_product', 'image', 'price', 'short_desc', 'quantity')
            ->orderBy('name_product')
            ->get();

        return view('shop', compact('products'));
    }

    public function addToCart(Request $request, CartService $cart): RedirectResponse
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect->with('error', 'Please log in before adding items to your cart.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $added = $cart->addProduct((int) $data['product_id'], max(1, (int) ($data['quantity'] ?? 1)));

        return redirect()
            ->route('shop')
            ->with($added ? 'success' : 'error', $added ? 'Product added to cart.' : 'Unable to add this product to cart.');
    }

    public function checkout(Request $request, CartService $cart): View|RedirectResponse
    {
        if ($request->filled('remove_id')) {
            $cart->remove((int) $request->query('remove_id'));

            return redirect()->route('checkout')->with('success', 'Item removed from cart.');
        }

        return view('checkout', [
            'cartItems' => $cart->refreshProducts(),
        ]);
    }

    public function placeOrder(Request $request, CartService $cart): RedirectResponse
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $data = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'payment' => ['required', 'in:Cash On Delivery,Online Banking'],
            'cart_state' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $cartState = $cart->parseCartState($data['cart_state'] ?? null);
        $selection = $cart->buildCheckoutSelection($cartState);
        $orderItems = $selection['items'];

        if ($orderItems === []) {
            return redirect()->route('checkout')->with('error', 'Please select at least one item to place an order.');
        }

        $receipt = '';

        if ($data['payment'] === 'Online Banking') {
            try {
                $receipt = $this->storeReceiptUpload($request);
            } catch (RuntimeException $exception) {
                return redirect()->route('checkout')->with('error', $exception->getMessage());
            }
        }

        $total = $selection['subtotal'] + $selection['shipping'] + $selection['tax'];

        try {
            DB::transaction(function () use ($data, $orderItems, $receipt, $total): void {
                $orderId = DB::table('orders')->insertGetId([
                    'username' => (string) $this->currentUser(),
                    'address' => $data['address'],
                    'state' => $data['state'],
                    'postcode' => $data['postcode'],
                    'city' => $data['city'],
                    'time_order' => now()->format('Y-m-d H:i:s'),
                    'payment_method' => $data['payment'],
                    'receipt' => $receipt,
                    'total' => $total,
                    'status' => 'Pending',
                ], 'order_id');

                foreach ($orderItems as $item) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'product' => $item['name_product'],
                        'quantity' => (int) $item['quantity'],
                    ]);

                    $updated = DB::table('product')
                        ->where('id', (int) $item['id'])
                        ->where('quantity', '>=', (int) $item['quantity'])
                        ->decrement('quantity', (int) $item['quantity']);

                    if ($updated !== 1) {
                        throw new RuntimeException('One or more products no longer have enough stock.');
                    }
                }
            });
        } catch (RuntimeException $exception) {
            return redirect()->route('checkout')->with('error', $exception->getMessage());
        }

        $cart->setCart($cart->selectedRemainingCart($cartState));

        return redirect()->route('order.success')->with('success', 'Order placed successfully.');
    }

    public function orderSuccess(): View
    {
        return view('order-success');
    }

    public function faq(): View
    {
        return view('faq', ['admin' => false]);
    }

    public function onlineBanking(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            ]);

            $this->storeReceiptUpload($request);

            return redirect()->route('online-banking')->with('success', 'Thank you for your upload!');
        }

        return view('online-banking');
    }

    private function storeReceiptUpload(Request $request): string
    {
        $file = $request->file('receipt');

        if (! $file || ! $file->isValid()) {
            throw new RuntimeException('Receipt upload failed.');
        }

        $extension = $file->extension();
        $filename = 'receipt_'.Str::random(16).'.'.$extension;
        $targetDir = public_path('assets/uploads');

        if (! is_dir($targetDir) && ! mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            throw new RuntimeException('Unable to prepare upload directory.');
        }

        $file->move($targetDir, $filename);

        return 'assets/uploads/'.$filename;
    }
}
