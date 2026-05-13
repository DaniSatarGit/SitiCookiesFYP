<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AdminController extends Controller
{
    public function products(): View|RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $products = DB::table('product')
            ->select('id', 'name_product', 'quantity', 'price')
            ->orderBy('name_product')
            ->get();

        return view('admin.products', compact('products'));
    }

    public function addProduct(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $data = $request->validate([
            'name_product' => ['required', 'string', 'max:255'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'short_desc' => ['required', 'string', 'max:500'],
        ]);

        try {
            $image = $this->storeProductImage($request, true);
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.products')->with('error', $exception->getMessage());
        }

        DB::table('product')->insert([
            'name_product' => $data['name_product'],
            'image' => $image,
            'quantity' => (int) $data['quantity'],
            'price' => (float) $data['price'],
            'short_desc' => $data['short_desc'],
        ]);

        return redirect()->route('admin.products')->with('success', 'Product added successfully.');
    }

    public function updateProduct(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'name_product' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'short_desc' => ['required', 'string', 'max:500'],
        ]);

        $update = [
            'name_product' => $data['name_product'],
            'quantity' => (int) $data['quantity'],
            'price' => (float) $data['price'],
            'short_desc' => $data['short_desc'],
        ];

        if ($request->hasFile('image')) {
            try {
                $update['image'] = $this->storeProductImage($request, false);
            } catch (RuntimeException $exception) {
                return redirect()->route('admin.products')->with('error', $exception->getMessage());
            }
        }

        DB::table('product')->where('id', (int) $data['product_id'])->update($update);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully.');
    }

    public function deleteProduct(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
        ]);

        DB::table('product')->where('id', (int) $data['product_id'])->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully.');
    }

    public function orders(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'order_id' => ['required', 'integer', 'min:1'],
                'status' => ['required', 'in:Pending,Shipping,Complete'],
            ]);

            DB::table('orders')->where('order_id', (int) $data['order_id'])->update([
                'status' => $data['status'],
            ]);

            return redirect()->route('admin.orders')->with('success', 'Order status updated successfully.');
        }

        $orders = DB::table('orders as o')
            ->join('order_items as oi', 'o.order_id', '=', 'oi.order_id')
            ->selectRaw("o.order_id, o.username, o.address, o.state, o.postcode, o.city, o.time_order, o.payment_method, o.receipt, o.total, o.status, GROUP_CONCAT(oi.product ORDER BY oi.product SEPARATOR ', ') AS products, GROUP_CONCAT(oi.quantity ORDER BY oi.product SEPARATOR ', ') AS quantities")
            ->groupBy('o.order_id', 'o.username', 'o.address', 'o.state', 'o.postcode', 'o.city', 'o.time_order', 'o.payment_method', 'o.receipt', 'o.total', 'o.status')
            ->orderByDesc('o.time_order')
            ->get();

        return view('admin.orders', compact('orders'));
    }

    public function dashboard(): View|RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $recentOrders = DB::table('orders as o')
            ->join('order_items as oi', 'o.order_id', '=', 'oi.order_id')
            ->selectRaw("o.order_id, o.username, o.time_order, o.payment_method, o.total, o.status, GROUP_CONCAT(oi.product ORDER BY oi.product SEPARATOR ', ') AS products, GROUP_CONCAT(oi.quantity ORDER BY oi.product SEPARATOR ', ') AS quantities")
            ->groupBy('o.order_id', 'o.username', 'o.time_order', 'o.payment_method', 'o.total', 'o.status')
            ->orderByDesc('o.time_order')
            ->limit(10)
            ->get();

        $startOfMonth = now()->startOfMonth()->format('Y-m-d H:i:s');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d H:i:s');
        $startOfWeek = now()->startOfWeek()->format('Y-m-d H:i:s');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d H:i:s');

        $monthStats = DB::table('orders')
            ->whereBetween('time_order', [$startOfMonth, $endOfMonth])
            ->selectRaw('COALESCE(SUM(total), 0) AS total_sales, COUNT(order_id) AS total_orders')
            ->first();

        $weekStats = DB::table('orders')
            ->whereBetween('time_order', [$startOfWeek, $endOfWeek])
            ->selectRaw('COALESCE(SUM(total), 0) AS total_sales, COUNT(order_id) AS total_orders')
            ->first();

        $monthlySalesData = DB::table('orders')
            ->selectRaw("DATE_FORMAT(time_order, '%Y-%m') AS month, SUM(total) AS total_sales")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $paymentMethodsData = DB::table('orders')
            ->selectRaw('payment_method, COUNT(order_id) AS method_count')
            ->groupBy('payment_method')
            ->get();

        $ordersByStateData = DB::table('orders')
            ->selectRaw('state, COUNT(order_id) AS order_count')
            ->groupBy('state')
            ->get();

        $averageOrderValueData = DB::table('orders')
            ->selectRaw("DATE_FORMAT(time_order, '%Y-%m') AS month, AVG(total) AS avg_order_value")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'recentOrders',
            'monthStats',
            'weekStats',
            'monthlySalesData',
            'paymentMethodsData',
            'ordersByStateData',
            'averageOrderValueData'
        ));
    }

    public function comments(): View|RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $comments = DB::table('comment')
            ->select('name', 'email', 'message', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('admin.comments', compact('comments'));
    }

    public function faq(): View|RedirectResponse
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('faq', ['admin' => true]);
    }

    private function storeProductImage(Request $request, bool $required): string
    {
        $file = $request->file('image');

        if (! $file || ! $file->isValid()) {
            if ($required) {
                throw new RuntimeException('Please upload a product image.');
            }

            throw new RuntimeException('Image upload failed.');
        }

        $filename = 'product_'.Str::random(16).'.'.$file->extension();
        $targetDir = public_path('assets/images');

        if (! is_dir($targetDir) && ! mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            throw new RuntimeException('Unable to prepare product image directory.');
        }

        $file->move($targetDir, $filename);

        return $filename;
    }
}
