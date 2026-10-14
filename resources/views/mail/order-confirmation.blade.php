@extends('mail.layouts.app')

@section('content')
<div style="background-color: #ffffff; border-radius: 8px; padding: 25px;">
    <h2 style="color: #333; margin-top: 0;">Thank you for your order!</h2>

    <p>Hi {{ $order->name }}, we've received your order.</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 6px 0;"><strong>Order Number</strong></td>
            <td style="padding: 6px 0; text-align: right;">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0;"><strong>Order Date</strong></td>
            <td style="padding: 6px 0; text-align: right;">{{ $order->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0;"><strong>Fulfillment</strong></td>
            <td style="padding: 6px 0; text-align: right;">{{ ucfirst($order->delivery_type) }}</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr style="border-bottom: 1px solid #ddd;">
                <th style="text-align: left; padding: 6px 0;">Item</th>
                <th style="text-align: center; padding: 6px 0;">Qty</th>
                <th style="text-align: right; padding: 6px 0;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 6px 0;">{{ $item->product_name }}</td>
                    <td style="text-align: center; padding: 6px 0;">{{ $item->quantity }}</td>
                    <td style="text-align: right; padding: 6px 0;">RM {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: right; font-size: 1.1rem;">
        <strong>Total: RM {{ number_format($order->total, 2) }}</strong>
    </p>

    <div style="text-align: center; margin: 30px 0; background-color: #f8f9fa; border-radius: 8px; padding: 16px;">
        <p style="margin: 0 0 8px;">Keep this order number to check your order status anytime:</p>
        <p style="margin: 0 0 16px; font-size: 1.2rem;"><strong>{{ $order->order_number }}</strong></p>
        <a href="{{ route('order.track', ['order_number' => $order->order_number]) }}"
           style="background-color: #d4a373; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            Track Your Order
        </a>
    </div>

    <p style="margin-top: 25px; font-size: 13px; color: #666;">
        If you didn't place this order, you can safely ignore this email.
    </p>
</div>
@endsection
