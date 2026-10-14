@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Order Details</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li><a href="{{ route('admin.orders') }}"><div class="text-tiny">Orders</div></a></li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li><div class="text-tiny">{{ $order->order_number }}</div></li>
                </ul>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-20">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <!-- Order Info -->
                <div class="col-lg-8">
                    <div class="wg-box mb-20">
                        <div class="flex items-center justify-between mb-20">
                            <h5>Order #{{ $order->order_number }}</h5>
                            <span class="badge {{ $order->status_badge_class }} badge-lg">
                                {{ $order->status_name }}
                            </span>
                        </div>

                        <div class="row mb-20">
                            <div class="col-md-6">
                                <h6 class="mb-10">Customer Information</h6>
                                <p><strong>Name:</strong> {{ $order->name }}</p>
                                <p><strong>Email:</strong> {{ $order->email }}</p>
                                <p><strong>Phone:</strong> {{ $order->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-10">Shipping Address</h6>
                                <p>{{ $order->address }}</p>
                                <p>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
                            </div>
                        </div>

                        @if($order->notes)
                            <div class="mb-20">
                                <h6 class="mb-10">Order Notes</h6>
                                <p class="text-muted">{{ $order->notes }}</p>
                            </div>
                        @endif

                        <!-- Order Items -->
                        <h6 class="mb-10">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td class="text-center">RM {{ number_format($item->price, 2) }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-center">RM {{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-center">RM {{ number_format($order->subtotal, 2) }}</td>
                                    </tr>
                                    @if($order->discount > 0)
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                            <td class="text-center text-danger">- RM {{ number_format($order->discount, 2) }}</td>
                                        </tr>
                                    @endif
                                    {{-- <tr>
                                        <td colspan="3" class="text-end"><strong>Tax (SST 6%):</strong></td>
                                        <td class="text-center">RM {{ number_format($order->tax, 2) }}</td>
                                    </tr> --}}
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                        <td class="text-center">
                                            @if($order->shipping > 0)
                                                RM {{ number_format($order->shipping, 2) }}
                                            @else
                                                <span class="text-success">Free</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-center"><strong>RM {{ number_format($order->total, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    @if($order->payment)
                        <div class="wg-box">
                            <h5 class="mb-20">Payment Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Method:</strong> {{ $order->payment->method_label }}</p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge {{ $order->payment->status === 'completed' ? 'badge-success' : ($order->payment->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                            {{ ucfirst($order->payment->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    @if($order->payment->transaction_id)
                                        <p><strong>Transaction ID:</strong> {{ $order->payment->transaction_id }}</p>
                                    @endif
                                    @if($order->payment->paid_at)
                                        <p><strong>Paid At:</strong> {{ $order->payment->paid_at->format('d M Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($order->payment->hasReceipt())
                                <div class="mt-20">
                                    <h6 class="mb-10">Proof of Payment</h6>
                                    @if($order->payment->receiptIsPdf())
                                        <a href="{{ $order->payment->receipt_url }}" target="_blank" class="btn btn-secondary">
                                            <i class="icon-file"></i> View Uploaded Receipt (PDF)
                                        </a>
                                    @else
                                        <a href="{{ $order->payment->receipt_url }}" target="_blank">
                                            <img src="{{ $order->payment->receipt_url }}" alt="Proof of payment"
                                                 style="max-width: 280px; border: 1px solid #e9ecef; border-radius: 6px;">
                                        </a>
                                    @endif
                                </div>
                            @endif

                            {{-- Manual bank-transfer confirmation --}}
                            @if(!$order->payment->isCompleted())
                                <form action="{{ route('admin.order.payment-done', $order->id) }}" method="POST" class="mt-20">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success">
                                        <i class="icon-check"></i> Mark Payment Done
                                    </button>
                                    <small class="text-muted d-block mt-1">
                                        Confirm only after you have verified the transfer in the bank account.
                                    </small>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Status Management -->
                <div class="col-lg-4">
                    <div class="wg-box mb-20">
                        <h5 class="mb-20">Update Status</h5>
                        <form action="{{ route('admin.order.status', $order->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-15">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-15">
                                <label class="form-label">Status Label / Note (Optional)</label>
                                <input type="text" name="status_label" class="form-control" 
                                       value="{{ $order->status_label }}"
                                       placeholder="e.g., Expected delivery: 2 Apr 2026">
                                <small class="text-muted">Add a custom label or note visible on the order</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="icon-check"></i> Update Status
                            </button>
                        </form>
                    </div>

                    <div class="wg-box">
                        <h5 class="mb-20">Order Timeline</h5>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <strong>Order Placed</strong>
                                    <p class="text-muted mb-0">{{ $order->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>

                            @if($order->payment && $order->payment->paid_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <strong>Payment Received</strong>
                                        <p class="text-muted mb-0">{{ $order->payment->paid_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($order->delivered_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <strong>Delivered</strong>
                                        <p class="text-muted mb-0">{{ $order->delivered_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($order->cancelled_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <strong>Cancelled</strong>
                                        <p class="text-muted mb-0">{{ $order->cancelled_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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
    .badge-lg {
        padding: 8px 15px;
        font-size: 14px;
    }
    .badge-info { background-color: #17a2b8; color: white; }
    .badge-primary { background-color: #007bff; color: white; }
    .badge-warning { background-color: #ffc107; color: #333; }
    .badge-success { background-color: #28a745; color: white; }
    .badge-danger { background-color: #dc3545; color: white; }
    .badge-secondary { background-color: #6c757d; color: white; }
    
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
    }
    
    .row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
    .col-lg-8 { flex: 0 0 66.666%; max-width: 66.666%; padding: 0 10px; }
    .col-lg-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0 10px; }
    .col-md-6 { flex: 0 0 50%; max-width: 50%; }
    
    @media (max-width: 992px) {
        .col-lg-8, .col-lg-4 { flex: 0 0 100%; max-width: 100%; }
    }
    
    .mb-15 { margin-bottom: 15px; }
    .mb-10 { margin-bottom: 10px; }
    .w-100 { width: 100%; }
    .text-end { text-align: right; }
    .gap10 { gap: 10px; }

    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 12px;
        bottom: -8px;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item:last-child::before {
        display: none;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .timeline-content {
        padding-left: 5px;
    }
    .bg-success { background-color: #28a745; }
    .bg-danger { background-color: #dc3545; }
    .bg-warning { background-color: #ffc107; }
    .bg-info { background-color: #17a2b8; }

    .table-primary {
        background-color: #cce5ff;
    }
    .text-danger { color: #dc3545; }
    .text-success { color: #28a745; }
</style>
@endpush
