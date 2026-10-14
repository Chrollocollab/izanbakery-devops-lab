@extends('layouts.app')
@section('content')
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="shop-checkout container">
            <div class="checkout-steps">
                <a href="{{ route('cart.index') }}" class="checkout-steps__item">
                    <span class="checkout-steps__item-number">01</span>
                    <span class="checkout-steps__item-title">
                        <span>Shopping Bag</span>
                        <em>Manage Your Items List</em>
                    </span>
                </a>
                <a href="javascript:void(0)" class="checkout-steps__item">
                    <span class="checkout-steps__item-number">02</span>
                    <span class="checkout-steps__item-title">
                        <span>Shipping and Checkout</span>
                        <em>Checkout Your Items List</em>
                    </span>
                </a>
                <a href="javascript:void(0)" class="checkout-steps__item active">
                    <span class="checkout-steps__item-number">03</span>
                    <span class="checkout-steps__item-title">
                        <span>Confirmation</span>
                        <em>Review And Submit Your Order</em>
                    </span>
                </a>
            </div>

            <div class="text-center" style="max-width:640px;margin:0 auto;padding:2rem 0;">
                <div style="font-size:3rem;color:var(--bk-success,#28a745);margin-bottom:1rem;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h2 class="page-title">Thank you for your order!</h2>

                @if(session('success'))
                    <p style="color:var(--bk-text-medium);font-size:1.05rem;">{{ session('success') }}</p>
                @endif

                <p style="font-size:1.05rem;margin-bottom:0.25rem;">
                    Order Number: <strong>{{ $order->order_number }}</strong>
                </p>
                <p style="color:var(--bk-text-medium);">
                    Status: <strong>{{ $order->status_name }}</strong>
                </p>

                <div class="alert alert-info" style="margin-top:1.5rem;text-align:left;">
                    Your payment is being verified. Once we confirm your bank transfer, your order
                    will be marked as paid and we'll start preparing it. Total paid:
                    <strong>RM {{ number_format($order->total, 2) }}</strong>.
                </div>

                <a href="{{ route('shop.index') }}" class="btn btn-primary mt-3">Continue Shopping</a>
            </div>
        </section>
    </main>
@endsection
