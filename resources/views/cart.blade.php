@extends('layouts.app')
@section('content')
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="shop-checkout container">
            <h2 class="page-title">{{ __('messages.cart.your_order') }}</h2>
            <div class="checkout-steps">
                <a href="javascript:void(0)" class="checkout-steps__item active">
                    <span class="checkout-steps__item-number">01</span>
                    <span class="checkout-steps__item-title">
                        <span>{{ __('messages.cart.step1_title') }}</span>
                        <em>{{ __('messages.cart.step1_sub') }}</em>
                    </span>
                </a>
                <a href="javascript:void(0)" class="checkout-steps__item">
                    <span class="checkout-steps__item-number">02</span>
                    <span class="checkout-steps__item-title">
                        <span>{{ __('messages.cart.step2_title') }}</span>
                        <em>{{ __('messages.cart.step2_sub') }}</em>
                    </span>
                </a>
                <a href="javascript:void(0)" class="checkout-steps__item">
                    <span class="checkout-steps__item-number">03</span>
                    <span class="checkout-steps__item-title">
                        <span>{{ __('messages.cart.step3_title') }}</span>
                        <em>{{ __('messages.cart.step3_sub') }}</em>
                    </span>
                </a>
            </div>
            <div class="shopping-cart">
                @if ($items->count() > 0)
                    <div class="cart-table__wrapper">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.cart.product') }}</th>
                                    <th></th>
                                    <th>{{ __('messages.cart.price') }}</th>
                                    <th>{{ __('messages.cart.quantity') }}</th>
                                    <th>{{ __('messages.cart.subtotal') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        <div class="shopping-cart__product-item">
                                            @if ($item->model && $item->model->getFirstMediaUrl('product', 'thumb'))
                                                <img loading="lazy" src="{{ $item->model->getFirstMediaUrl('product', 'thumb') }}" width="120"
                                                    height="120" alt="" />
                                            @else
                                                <div class="bkr-img-placeholder bkr-img-placeholder--sm" style="width: 120px; height: 120px;">
                                                    <i class="bi bi-cake2" aria-hidden="true"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="shopping-cart__product-item__detail">
                                            <h4>{{ $item->name }}</h4>
                                            @if($item->model->category ?? false)
                                            <ul class="shopping-cart__product-item__options">
                                                <li>{{ $item->model->category->name }}</li>
                                            </ul>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="shopping-cart__product-price">RM{{$item->price}}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('cart.update', $item->rowId) }}" method="POST"
                                            class="qty-control position-relative">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" name="quantity" value="{{ $item->qty - 1 }}"
                                                class="qty-control__reduce" aria-label="Decrease quantity"
                                                style="border:0;background:transparent;line-height:1;{{ $item->qty <= 1 ? 'opacity:.35;pointer-events:none;' : '' }}"
                                                {{ $item->qty <= 1 ? 'disabled' : '' }}>-</button>
                                            <input type="text" value="{{ $item->qty }}" readonly tabindex="-1"
                                                aria-label="Quantity"
                                                class="qty-control__number text-center"
                                                style="border:0;pointer-events:none;">
                                            <button type="submit" name="quantity" value="{{ $item->qty + 1 }}"
                                                class="qty-control__increase" aria-label="Increase quantity"
                                                style="border:0;background:transparent;line-height:1;">+</button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="shopping-cart__subtotal">RM{{$item->subtotal()}}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('cart.remove', $item->rowId) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="remove-cart" style="background:none;border:0;cursor:pointer;">
                                                <svg width="10" height="10" viewBox="0 0 10 10" fill="#767676"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M0.259435 8.85506L9.11449 0L10 0.885506L1.14494 9.74056L0.259435 8.85506Z" />
                                                    <path
                                                        d="M0.885506 0.0889838L9.74057 8.94404L8.85506 9.82955L0 0.97449L0.885506 0.0889838Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{-- <div class="cart-table-footer">
                            <form action="#" class="position-relative bg-body">
                                <input class="form-control" type="text" name="coupon_code" placeholder="Coupon Code">
                                <input class="btn-link fw-medium position-absolute top-0 end-0 h-100 px-4" type="submit"
                                    value="APPLY COUPON">
                            </form>
                            <button class="btn btn-light">UPDATE CART</button>
                        </div> --}}
                    </div>
                    <div class="shopping-cart__totals-wrapper">
                    <div class="sticky-content">
                        @php
                            $cartSubtotal = (float) Cart::instance('cart')->subtotal(2, '.', '');
                            $deliveryReward = \App\Support\DeliveryReward::forSubtotal($cartSubtotal);
                        @endphp
                        <div class="shopping-cart__totals">
                            <h3>{{ __('messages.cart.order_summary') }}</h3>
                            <table class="cart-totals">
                                <tbody>
                                    <tr>
                                        <th>{{ __('messages.cart.subtotal') }}</th>
                                        <td>RM{{ Cart::instance('cart')->subtotal() }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('messages.cart.delivery') }}</th>
                                        <td @if($deliveryReward['has_reward']) style="color:var(--bk-success);font-weight:600;" @else style="font-size:0.82rem;color:var(--bk-text-medium);font-weight:400;" @endif>
                                            {{ $deliveryReward['label'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="font-size:1rem;font-weight:700;color:var(--bk-text-dark);">{{ __('messages.cart.total') }}</th>
                                        <td style="font-size:1rem;font-weight:700;color:var(--bk-primary);">
                                            RM{{ Cart::instance('cart')->subtotal() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mobile_fixed-btn_wrapper">
                            <div class="button-wrapper container">
                                <a href="{{ route('cart.checkout') }}" class="btn btn-primary btn-checkout">{{ __('messages.cart.proceed_checkout') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    <div class="row">
                        <div class="col-md-12 text-center pt-5 pb-5">
                            <p style="font-size:1.1rem;color:var(--bk-text-medium);margin-bottom:1.5rem;">
                                {{ __('messages.cart.empty_message') }}
                            </p>
                            <a href="{{ route('shop.index') }}" class="bakery-btn-primary">{{ __('messages.cart.browse_menu') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection
