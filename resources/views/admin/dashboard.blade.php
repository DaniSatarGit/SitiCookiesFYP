@extends('layouts.site', ['adminLayout' => true, 'active' => 'dashboard'])

@section('title', 'Admin Dashboard - Siti Cookies')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
    <main class="container">
        <h1>Admin Dashboard</h1>

        <div class="grid stats">
            <div class="stat"><p>Total Sales</p><p class="value">RM{{ number_format((float) $monthStats->total_sales, 2) }}</p><p>This month</p></div>
            <div class="stat"><p>Total Sales</p><p class="value">RM{{ number_format((float) $weekStats->total_sales, 2) }}</p><p>This week</p></div>
            <div class="stat"><p>Orders</p><p class="value">{{ (int) $monthStats->total_orders }}</p><p>This month</p></div>
            <div class="stat"><p>Orders</p><p class="value">{{ (int) $weekStats->total_orders }}</p><p>This week</p></div>
        </div>

        <section class="table-card" style="margin-top:14px;">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Time Order</th>
                        <th>Payment Method</th>
                        <th>Products</th>
                        <th>Quantities</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->username }}</td>
                            <td>{{ $order->time_order }}</td>
                            <td>{{ $order->payment_method }}</td>
                            <td>{{ $order->products }}</td>
                            <td>{{ $order->quantities }}</td>
                            <td>RM{{ number_format((float) $order->total, 2) }}</td>
                            <td>{{ $order->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="grid chart-grid" style="margin-top:14px;">
            <div class="chart-container"><canvas id="monthlySalesChart"></canvas></div>
            <div class="chart-container"><canvas id="paymentMethodsChart"></canvas></div>
            <div class="chart-container"><canvas id="ordersByStateChart"></canvas></div>
            <div class="chart-container"><canvas id="aovChart"></canvas></div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        const monthlySalesLabels = @json($monthlySalesData->pluck('month'));
        const monthlySalesValues = @json($monthlySalesData->pluck('total_sales')->map(fn ($value) => (float) $value));
        const paymentMethodLabels = @json($paymentMethodsData->pluck('payment_method'));
        const paymentMethodValues = @json($paymentMethodsData->pluck('method_count')->map(fn ($value) => (int) $value));
        const stateLabels = @json($ordersByStateData->pluck('state'));
        const stateOrderValues = @json($ordersByStateData->pluck('order_count')->map(fn ($value) => (int) $value));
        const aovLabels = @json($averageOrderValueData->pluck('month'));
        const aovValues = @json($averageOrderValueData->pluck('avg_order_value')->map(fn ($value) => (float) $value));

        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: { labels: monthlySalesLabels, datasets: [{ label: 'Monthly Sales Growth', data: monthlySalesValues, borderColor: 'rgba(54, 162, 235, 1)', backgroundColor: 'rgba(54, 162, 235, 0.2)', fill: false }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('paymentMethodsChart'), {
            type: 'pie',
            data: { labels: paymentMethodLabels, datasets: [{ data: paymentMethodValues, backgroundColor: ['rgba(75, 192, 192, 0.3)', 'rgba(255, 99, 132, 0.3)', 'rgba(255, 206, 86, 0.3)'] }] },
            options: { responsive: true }
        });

        new Chart(document.getElementById('ordersByStateChart'), {
            type: 'bar',
            data: { labels: stateLabels, datasets: [{ label: 'Orders by State', data: stateOrderValues, backgroundColor: 'rgba(153, 102, 255, 0.3)', borderColor: 'rgba(153, 102, 255, 1)', borderWidth: 1 }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('aovChart'), {
            type: 'bar',
            data: { labels: aovLabels, datasets: [{ label: 'Average Order Value', data: aovValues, backgroundColor: 'rgba(255, 159, 64, 0.3)', borderColor: 'rgba(255, 159, 64, 1)', borderWidth: 1 }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
@endpush
