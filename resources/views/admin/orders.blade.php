@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Order Management</h3>
                <div class="flex items-center gap10">
                    <a href="{{ route('admin.order.create') }}" class="btn btn-primary">
                        <i class="icon-plus"></i> Create Order
                    </a>
                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                        <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                        <li><i class="icon-chevron-right"></i></li>
                        <li><div class="text-tiny">Orders</div></li>
                    </ul>
                </div>
            </div>

            <div class="wg-box">
                <!-- Search and Filter -->
                <form action="{{ route('admin.orders') }}" method="GET" class="mb-20">
                    <div class="flex gap10 flex-wrap">
                        <div class="flex-grow">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by order number, customer name, email, phone, or product..."
                                   value="{{ $search ?? '' }}">
                        </div>
                        <div>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ ($status ?? '') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-search"></i> Filter
                        </button>
                        @if($search || $status)
                            <a href="{{ route('admin.orders') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>

                @if($search || $status)
                    <div class="alert alert-info mb-20">
                        Showing {{ $orders->total() }} orders
                        @if($search) matching "<strong>{{ $search }}</strong>"@endif
                        @if($status) with status "<strong>{{ $statuses[$status] ?? $status }}</strong>"@endif
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success mb-20">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Order No</th>
                                    <th>Customer</th>
                                    <th class="text-center">Phone</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.order.show', $order->id) }}" class="fw-bold">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ $order->name }}</div>
                                            <small class="text-muted">{{ $order->email }}</small>
                                        </td>
                                        <td class="text-center">{{ $order->phone }}</td>
                                        <td class="text-center">RM {{ number_format($order->total, 2) }}</td>
                                        <td class="text-center">{{ $order->items->count() }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status_name }}
                                            </span>
                                            @if($order->status_label)
                                                <br><small class="text-muted">{{ $order->status_label }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $order->created_at->format('d M Y') }}
                                            <br><small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.order.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="View order">
                                                <i class="icon-eye"></i>
                                            </a>
                                            @if (in_array($order->status, ['paid', 'ready_for_pickup', 'completed']))
                                                <a href="{{ route('admin.order.receipt', $order->id) }}" class="btn btn-sm btn-outline-success" title="Download receipt" target="_blank">
                                                    <i class="icon-file-text"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            No orders found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $orders->appends(['search' => $search, 'status' => $status])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-info { background-color: #17a2b8; color: white; }
    .badge-primary { background-color: #007bff; color: white; }
    .badge-warning { background-color: #ffc107; color: #333; }
    .badge-success { background-color: #28a745; color: white; }
    .badge-danger { background-color: #dc3545; color: white; }
    .badge-secondary { background-color: #6c757d; color: white; }
    .fw-bold { font-weight: 600; }
    .gap10 { gap: 10px; }
    .flex-grow { flex-grow: 1; }
    .alert-info {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 10px 15px;
        border-radius: 4px;
    }
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
    }
</style>
@endpush
