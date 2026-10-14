{{-- Shared order status cards, used by the authenticated order-detail page
     and the guest order-tracking result page. Expects $order (with items,
     payment loaded). --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Order Summary</h5>
        <span class="badge {{ $order->status_badge_class }}">{{ $order->status_name }}</span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Order Date:</strong>
                <p>{{ $order->created_at->format('F d, Y h:i A') }}</p>
            </div>
            <div class="col-md-4">
                <strong>Fulfillment:</strong>
                <p>{{ ucfirst($order->delivery_type) }}</p>
            </div>
            <div class="col-md-4">
                <strong>{{ $order->delivery_type === 'delivery' ? 'Delivery Address' : 'Pickup Location' }}:</strong>
                <p>{{ $order->address ?? 'N/A' }}</p>
            </div>
        </div>
        @if($order->notes)
            <div class="mb-2">
                <strong>Notes:</strong>
                <p class="text-muted">{{ $order->notes }}</p>
            </div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Items</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-center">RM {{ number_format($item->price, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">RM {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <table class="table table-borderless w-auto mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted">Subtotal</td>
                        <td class="text-end">RM {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount > 0)
                        <tr>
                            <td class="text-muted">Discount</td>
                            <td class="text-end">-RM {{ number_format($order->discount, 2) }}</td>
                        </tr>
                    @endif
                    @if($order->shipping > 0)
                        <tr>
                            <td class="text-muted">Delivery Fee</td>
                            <td class="text-end">RM {{ number_format($order->shipping, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-end"><strong>RM {{ number_format($order->total, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Payment</h5>
        @if(($receiptUrl ?? null) && $order->isReceiptAvailable())
            <a href="{{ $receiptUrl }}" class="btn btn-sm btn-primary" target="_blank">
                <i class="bi bi-filetype-pdf"></i> Download Receipt
            </a>
        @endif
    </div>
    <div class="card-body">
        @if($order->payment)
            <div class="row">
                <div class="col-md-4">
                    <strong>Method:</strong>
                    <p>{{ $order->payment->method_label }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong>
                    <p>
                        <span class="badge {{ $order->payment->status_badge }}">
                            {{ ucfirst($order->payment->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <strong>Amount:</strong>
                    <p>RM {{ number_format($order->payment->amount, 2) }}</p>
                </div>
            </div>
            @if($order->payment->isCompleted() && $order->payment->paid_at)
                <p class="text-muted mb-0">Paid on {{ $order->payment->paid_at->format('F d, Y h:i A') }}</p>
            @else
                <div class="alert alert-info mb-0">
                    Your payment is being verified. We'll update this order once it's confirmed.
                </div>
            @endif
        @else
            <p class="text-muted mb-0">No payment record found for this order.</p>
        @endif
    </div>
</div>
