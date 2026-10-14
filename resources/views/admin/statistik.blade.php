@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Dashboard Statistik</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Statistik</div></li>
            </ul>
        </div>

        {{-- Sales & Revenue Section --}}
        <div class="wg-box mb-20">
            <div class="flex items-center justify-between mb-20">
                <h5>Jualan & Hasil</h5>
            </div>
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Jualan Hari Ini</div>
                                    <h4>RM {{ number_format($salesRevenue['today'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Jualan Minggu Ini</div>
                                    <h4>RM {{ number_format($salesRevenue['weekly'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-trending-up"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Jualan Bulan Ini</div>
                                    <h4>RM {{ number_format($salesRevenue['monthly'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-bar-chart-2"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Purata Nilai Pesanan</div>
                                    <h4>RM {{ number_format($salesRevenue['aov'], 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Orders Overview Section --}}
        <div class="wg-box mb-20">
            <div class="flex items-center justify-between mb-20">
                <h5>Gambaran Pesanan</h5>
            </div>
            <div class="row">
                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                    <div class="wg-chart-default bg-info-subtle">
                        <div class="text-center">
                            <div class="body-text mb-2">Pesanan Baru</div>
                            <h3 class="text-info">{{ $ordersOverview['new'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                    <div class="wg-chart-default bg-warning-subtle">
                        <div class="text-center">
                            <div class="body-text mb-2">Dalam Proses</div>
                            <h3 class="text-warning">{{ $ordersOverview['pending'] + $ordersOverview['processing'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                    <div class="wg-chart-default bg-success-subtle">
                        <div class="text-center">
                            <div class="body-text mb-2">Selesai</div>
                            <h3 class="text-success">{{ $ordersOverview['completed'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                    <div class="wg-chart-default bg-danger-subtle">
                        <div class="text-center">
                            <div class="body-text mb-2">Dibatalkan</div>
                            <h3 class="text-danger">{{ $ordersOverview['cancelled'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12">
                    <div class="wg-chart-default">
                        <div class="text-center">
                            <div class="body-text mb-2">Jumlah Pesanan</div>
                            <h3>{{ $ordersOverview['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Revenue Trend Chart --}}
            <div class="col-xl-8 col-lg-7">
                <div class="wg-box mb-20">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Trend Hasil (30 Hari Terakhir)</h5>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            {{-- Order Status Distribution --}}
            <div class="col-xl-4 col-lg-5">
                <div class="wg-box mb-20">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Taburan Status Pesanan</h5>
                    </div>
                    <div class="chart-container">
                        <canvas id="orderStatusChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Top Selling Products --}}
            <div class="col-xl-6 col-lg-6">
                <div class="wg-box mb-20">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Produk Terlaris</h5>
                    </div>
                    <div class="wg-table table-all-user">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Produk</th>
                                        <th>Terjual</th>
                                        <th>Hasil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productPerformance['best_selling'] as $index => $product)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td><span class="badge bg-success">{{ $product->total_sold }}</span></td>
                                        <td>RM {{ number_format($product->total_revenue, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tiada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Least Selling Products --}}
            <div class="col-xl-6 col-lg-6">
                <div class="wg-box mb-20">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Produk Kurang Laris</h5>
                    </div>
                    <div class="wg-table table-all-user">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Produk</th>
                                        <th>Terjual</th>
                                        <th>Hasil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productPerformance['least_selling'] as $index => $product)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td><span class="badge bg-warning">{{ $product->total_sold }}</span></td>
                                        <td>RM {{ number_format($product->total_revenue, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tiada data</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Performance --}}
        <div class="wg-box mb-20">
            <div class="flex items-center justify-between mb-20">
                <h5>Prestasi Kategori</h5>
            </div>
            <div class="row">
                @forelse($productPerformance['category_performance'] as $category)
                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
                    <div class="wg-chart-default" style="border-left: 4px solid #007bff;">
                        <div class="text-center">
                            <div class="body-text mb-2">{{ $category->name }}</div>
                            <h5>RM {{ number_format($category->total_revenue, 2) }}</h5>
                            <small class="text-muted">{{ $category->total_sold }} terjual</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p class="text-center text-muted">Tiada data kategori</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="row">
            {{-- Customer Insights --}}
            <div class="col-xl-6 col-lg-6">
                <div class="wg-box mb-20">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Wawasan Pelanggan</h5>
                    </div>
                    <div class="row mb-20">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary">{{ $customerInsights['total'] }}</h4>
                                <small>Jumlah Pelanggan</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success">{{ $customerInsights['new'] }}</h4>
                                <small>Pelanggan Baru</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-info">{{ $customerInsights['returning'] }}</h4>
                                <small>Pelanggan Berulang</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-warning">{{ $customerInsights['purchase_frequency'] }}</h4>
                                <small>Kekerapan Beli</small>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Pelanggan Teratas</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Pesanan</th>
                                    <th>Jumlah Belanja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customerInsights['top_customers'] as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->order_count }}</td>
                                    <td>RM {{ number_format($customer->total_spent, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Tiada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Payment & Financial --}}
            <div class="col-xl-6 col-lg-6">
                <div class="wg-box mb-20">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Pembayaran & Kewangan</h5>
                    </div>
                    
                    <div class="row mb-20">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-success-subtle rounded">
                                <h4 class="text-success">RM {{ number_format($paymentFinancial['today_income'], 2) }}</h4>
                                <small>Pendapatan Hari Ini</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-info-subtle rounded">
                                <h4 class="text-info">{{ $paymentFinancial['refund_count'] }}</h4>
                                <small>Bayaran Balik</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="text-center p-3 bg-danger-subtle rounded">
                                <h4 class="text-danger">{{ $paymentFinancial['failed_payments'] }}</h4>
                                <small>Pembayaran Gagal</small>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3">Kaedah Pembayaran</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Kaedah</th>
                                    <th>Bilangan</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentFinancial['payment_methods'] as $method)
                                <tr>
                                    <td>
                                        @switch($method->method)
                                            @case('fpx')
                                                <span class="badge bg-primary">FPX</span>
                                                @break
                                            @case('card')
                                                <span class="badge bg-info">Kad</span>
                                                @break
                                            @case('cash')
                                                <span class="badge bg-success">Tunai</span>
                                                @break
                                            @case('ewallet')
                                                <span class="badge bg-warning">E-Wallet</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $method->count }}</td>
                                    <td>RM {{ number_format($method->total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Tiada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="wg-box">
            <div class="flex items-center justify-between mb-20">
                <h5>Pesanan Terkini</h5>
            </div>
            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Jumlah</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th>Tarikh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->user->name ?? $order->name }}</td>
                                <td>RM {{ number_format($order->total, 2) }}</td>
                                <td>
                                    @if($order->payment)
                                        <span class="badge {{ $order->payment->status_badge }}">
                                            {{ ucfirst($order->payment->status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tiada pesanan terkini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .wg-chart-default {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .ic-bg {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e8f4ff;
        border-radius: 10px;
    }
    .ic-bg i {
        font-size: 24px;
        color: #007bff;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .bg-info-subtle { background-color: #cff4fc !important; }
    .bg-warning-subtle { background-color: #fff3cd !important; }
    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-danger-subtle { background-color: #f8d7da !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Trend Chart
    const revenueTrendData = @json($revenueTrend);
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueTrendData.map(item => item.date),
            datasets: [{
                label: 'Hasil (RM)',
                data: revenueTrendData.map(item => item.revenue),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value;
                        }
                    }
                }
            }
        }
    });

    // Order Status Distribution Chart
    const orderStatusData = @json($orderStatusDistribution);
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(orderStatusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                data: Object.values(orderStatusData),
                backgroundColor: [
                    '#ffc107', // pending_payment - warning
                    '#28a745', // paid - success
                    '#17a2b8', // ready_for_pickup - info
                    '#20c997', // completed - teal
                    '#dc3545'  // cancelled - danger
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
