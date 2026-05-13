@extends('layouts.site', ['adminLayout' => true, 'active' => 'orders'])

@section('title', 'Admin Orders - Siti Cookies')

@section('content')
    <main class="container" style="max-width:1400px;">
        <section class="table-card">
            <h1>Admin Orders</h1>
            <table style="min-width:1100px;">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Username</th>
                        <th>Address</th>
                        <th>State</th>
                        <th>Postcode</th>
                        <th>City</th>
                        <th>Time Order</th>
                        <th>Payment Method</th>
                        <th>Receipt</th>
                        <th>Total</th>
                        <th>Products</th>
                        <th>Quantities</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ (int) $order->order_id }}</td>
                            <td>{{ $order->username }}</td>
                            <td>{{ $order->address }}</td>
                            <td>{{ $order->state }}</td>
                            <td>{{ $order->postcode }}</td>
                            <td>{{ $order->city }}</td>
                            <td>{{ $order->time_order }}</td>
                            <td>{{ $order->payment_method }}</td>
                            <td>
                                @if (! empty($order->receipt))
                                    <a href="{{ asset($order->receipt) }}" target="_blank" rel="noopener noreferrer">View receipt</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>RM{{ number_format((float) $order->total, 2) }}</td>
                            <td>{{ $order->products }}</td>
                            <td>{{ $order->quantities }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.orders') }}">
                                    @csrf
                                    <select name="status">
                                        @foreach (['Pending', 'Shipping', 'Complete'] as $status)
                                            <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="order_id" value="{{ (int) $order->order_id }}">
                                    <button type="submit" style="margin-top:4px;">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="13">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
@endsection
