<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 34px; }
        * { box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 12.5px; color: #333; }

        /* ── Header ─────────────────────────────────────────── */
        .header-brand { text-align: center; margin-bottom: 18px; }
        .logo-badge { width: 190px; height: auto; }

        .header-meta { text-align: center; margin-bottom: 16px; }
        .receipt-title {
            font-family: Georgia, 'Times New Roman', serif; font-size: 18px; letter-spacing: 5px;
            color: #3B2417; margin: 0 0 10px 0;
        }
        .receipt-order-no { font-size: 11.5px; color: #777; margin: 0 0 12px 0; }
        .status-pill {
            display: inline-block; padding: 4px 16px; border-radius: 20px;
            font-size: 10px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase;
            border: 1px solid; white-space: nowrap;
        }

        .divider { border-top: 3px solid #3B2417; margin: 4px 0 20px 0; }

        /* ── Info sections ──────────────────────────────────── */
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.info td { width: 50%; vertical-align: top; padding: 0 10px 0 0; }
        .info-label {
            font-size: 10px; font-weight: bold; letter-spacing: 1px; color: #BE6A36;
            text-transform: uppercase; padding-bottom: 8px; margin-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-row { font-size: 12px; margin: 7px 0; color: #333; line-height: 1.4; }
        .info-row .k { display: inline-block; width: 55px; color: #999; }

        /* ── Order notes callout ────────────────────────────── */
        .notes-box {
            background-color: #FBF4EC; border-left: 3px solid #BE6A36;
            padding: 10px 14px; margin-bottom: 18px;
        }
        .notes-label {
            font-size: 10px; font-weight: bold; letter-spacing: 1px; color: #BE6A36;
            text-transform: uppercase; margin: 0 0 3px 0;
        }
        .notes-text { font-size: 12px; color: #444; margin: 0; }

        /* ── Items table ────────────────────────────────────── */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.items th {
            color: #3B2417; text-align: left; font-weight: bold;
            font-size: 10px; letter-spacing: 0.6px; text-transform: uppercase;
            padding: 8px 8px; border-bottom: 2px solid #3B2417;
        }
        table.items th.right, table.items td.right { text-align: right; }
        table.items th.center, table.items td.center { text-align: center; }
        table.items td { padding: 8px; font-size: 12px; border-bottom: 1px solid #f0f0f0; }
        table.items tr.odd td { background-color: #FAF8F5; }

        /* ── Totals ─────────────────────────────────────────── */
        table.totals { width: 280px; margin-left: auto; margin-bottom: 22px; border-collapse: collapse; }
        table.totals td { padding: 3px 4px; font-size: 12px; }
        table.totals td.label { text-align: right; color: #555; width: 55%; }
        table.totals td.value { text-align: right; width: 45%; white-space: nowrap; }
        .free { color: #1e8a4c; font-weight: bold; }
        tr.grand-total td { padding: 8px 4px; border-top: 2px solid #3B2417; }
        tr.grand-total td.label { font-size: 12px; font-weight: bold; color: #3B2417; padding-left: 10px; }
        tr.grand-total td.value { font-size: 15px; font-weight: bold; color: #3B2417; padding-right: 10px; }

        /* ── Footer info ─────────────────────────────────────── */
        .footer-divider { border-top: 1px solid #eee; margin: 4px 0 14px 0; }
        table.footer-info { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.footer-info td { width: 50%; vertical-align: top; padding: 0 10px 0 0; }

        .thanks { text-align: center; margin-top: 6px; }
        .thanks p.msg {
            font-family: Georgia, 'Times New Roman', serif; font-size: 14px; color: #3B2417; margin: 0 0 4px 0;
        }
        .thanks p.sub { font-size: 10px; color: #aaa; letter-spacing: 0.5px; margin: 0; text-transform: uppercase; }
    </style>
</head>
<body>

    @php
        $statusColors = match($order->status) {
            \App\Models\Order::STATUS_PENDING_PAYMENT => ['#FDF3E3', '#E8A84C', '#8A5A00'],
            \App\Models\Order::STATUS_PAID             => ['#E9F7EF', '#28a745', '#1e7e34'],
            \App\Models\Order::STATUS_READY_FOR_PICKUP => ['#E8F1FB', '#3B82C4', '#255a86'],
            \App\Models\Order::STATUS_COMPLETED         => ['#E9F7EF', '#28a745', '#1e7e34'],
            \App\Models\Order::STATUS_CANCELLED         => ['#FBEAEA', '#dc3545', '#a52834'],
            default => ['#F1F1F1', '#999999', '#666666'],
        };
        [$statusBg, $statusBorder, $statusText] = $statusColors;

        $isPickup = strcasecmp(trim($order->address ?? ''), 'Store pickup') === 0;
    @endphp

    <!-- Header -->
    <div class="header-brand">
        <img class="logo-badge" src="{{ public_path('images/logo/logo-mark.png') }}" alt="{{ config('app.name') }}">
    </div>

    <div class="header-meta">
        <p class="receipt-title">RECEIPT</p>
        <p class="receipt-order-no">Order #{{ $order->order_number }}</p>
        <span class="status-pill" style="background-color:{{ $statusBg }}; border-color:{{ $statusBorder }}; color:{{ $statusText }};">
            {{ $order->status_name }}
        </span>
    </div>

    <div class="divider"></div>

    <!-- Customer / Fulfillment -->
    <table class="info">
        <tr>
            <td>
                <p class="info-label">Customer Information</p>
                <p class="info-row"><span class="k">Name</span> {{ $order->name }}</p>
                @if($order->email)
                    <p class="info-row"><span class="k">Email</span> {{ $order->email }}</p>
                @endif
                <p class="info-row"><span class="k">Phone</span> {{ $order->phone }}</p>
            </td>
            <td>
                <p class="info-label">{{ $isPickup ? 'Pickup' : 'Shipping Address' }}</p>
                @if($isPickup)
                    <p class="info-row">Self-collection at {{ config('app.name') }}</p>
                @else
                    <p class="info-row">{{ $order->address }}</p>
                    @if($order->city || $order->state || $order->postal_code)
                        <p class="info-row">{{ collect([$order->city, $order->state, $order->postal_code])->filter()->join(', ') }}</p>
                    @endif
                @endif
            </td>
        </tr>
    </table>

    @if($order->notes)
        <div class="notes-box">
            <p class="notes-label">Order Notes</p>
            <p class="notes-text">{{ $order->notes }}</p>
        </div>
    @endif

    <!-- Items -->
    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th class="right">Price</th>
                <th class="center">Qty</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $i => $item)
                <tr class="{{ $i % 2 === 1 ? 'odd' : '' }}">
                    <td>{{ $item->product_name }}</td>
                    <td class="right">RM {{ number_format($item->price, 2) }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">RM {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">RM {{ number_format($order->subtotal, 2) }}</td>
        </tr>
        @if($order->discount > 0)
            <tr>
                <td class="label">Discount</td>
                <td class="value">- RM {{ number_format($order->discount, 2) }}</td>
            </tr>
        @endif
        @if(! $isPickup)
            <tr>
                <td class="label">Shipping</td>
                <td class="value">
                    @if($order->shipping > 0)
                        RM {{ number_format($order->shipping, 2) }}
                    @else
                        <span class="free">Free</span>
                    @endif
                </td>
            </tr>
        @endif
        <tr class="grand-total">
            <td class="label">Total</td>
            <td class="value">RM {{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <div class="footer-divider"></div>

    <!-- Payment / Order info -->
    <table class="footer-info">
        <tr>
            <td>
                <p class="info-label">Payment Information</p>
                @if($order->payment)
                    <p class="info-row"><span class="k">Method</span> {{ $order->payment->method_label }}</p>
                    <p class="info-row"><span class="k">Status</span> {{ ucfirst($order->payment->status) }}</p>
                    <p class="info-row"><span class="k">Paid At</span> {{ optional($order->payment->paid_at)->format('d M Y, h:i A') ?? '-' }}</p>
                @else
                    {{-- <p class="info-row"><span class="k">Method</span> Confirmed by admin</p> --}}
                    <p class="info-row"><span class="k">Status :</span> {{ $order->status_name }}</p>
                @endif
            </td>
            <td>
                <p class="info-label">Order Info</p>
                <p class="info-row"><span class="k">Placed :</span> {{ $order->created_at->format('d M Y, h:i A') }}</p>
                <p class="info-row"><span class="k">Type :</span> {{ $isPickup ? 'Self Pickup' : 'Delivery' }}</p>
                @if($order->delivered_at)
                    <p class="info-row"><span class="k">Delivered</span> {{ $order->delivered_at->format('d M Y, h:i A') }}</p>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer-divider"></div>

    <div class="thanks">
        <p class="msg">Thank you for your order</p>
        <p class="sub">{{ strtoupper(config('app.name')) }} &nbsp;&middot;&nbsp; This is a computer-generated receipt</p>
    </div>

</body>
</html>
