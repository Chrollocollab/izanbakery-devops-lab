@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .checkout-form-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f0e6d6;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }
    .checkout-form-card h5 {
        color: var(--bk-primary);
        font-weight: 700;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f0e6d6;
    }
    .checkout-form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--bk-text-dark, #2c2c2c);
        margin-bottom: 0.4rem;
    }
    .checkout-form-control {
        border: 1.5px solid #e0d4c3;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        font-size: 0.9375rem;
        width: 100%;
        transition: border-color 0.2s;
    }
    .checkout-form-control:focus {
        border-color: var(--bk-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(193,123,47,0.12);
    }
    select.checkout-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23C17B2F' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }
    .checkout-form-control.is-invalid {
        border-color: #d9534f;
    }
    .checkout-form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(217,83,79,0.12);
    }
    .field-error {
        display: none;
        color: #d9534f;
        font-size: 0.8rem;
        margin-top: 0.35rem;
    }
    .field-error.show {
        display: block;
    }
    #address-section {
        display: none;
    }
    .address-wrap {
        position: relative;
    }
    .address-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1.5px solid var(--bk-primary);
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 210px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        display: none;
    }
    .address-suggestions.open { display: block; }
    .address-suggestion-item {
        padding: 0.6rem 1rem;
        font-size: 0.875rem;
        cursor: pointer;
        border-bottom: 1px solid #f0e6d6;
        color: var(--bk-text-dark, #2c2c2c);
    }
    .address-suggestion-item:last-child { border-bottom: none; }
    .address-suggestion-item:hover,
    .address-suggestion-item.active {
        background: #fdf6ee;
        color: var(--bk-primary);
    }
    .address-suggestion-status {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        color: #aaa;
        text-align: center;
    }
    #address-map-box {
        display: none;
        margin-top: 0.75rem;
        border-radius: 10px;
        overflow: hidden;
        border: 1.5px solid #e0d4c3;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }
    #address-map-box.show {
        display: block;
        animation: mapIn 0.25s ease;
    }
    @keyframes mapIn {
        from { opacity: 0; transform: translateY(-5px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    #address-map {
        height: 240px;
        width: 100%;
    }
    #address-map-footer {
        background: #fdf6ee;
        border-top: 1px solid #f0e6d6;
        padding: 0.45rem 0.85rem;
        font-size: 0.78rem;
        color: #888;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    #address-map-footer i { color: var(--bk-primary); }
    .payment-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1600;
        align-items: center;
        justify-content: center;
    }
    .payment-modal-backdrop.active {
        display: flex;
    }
    .payment-modal {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        position: relative;
        text-align: center;
        box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    }
    .payment-modal__close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.4rem;
        cursor: pointer;
        color: #888;
        line-height: 1;
    }
    .payment-modal h6 {
        color: var(--bk-primary);
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .payment-modal .account-detail {
        background: #fdf6ee;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 0.5rem;
        text-align: left;
    }
    .payment-modal .account-detail dt {
        font-size: 0.78rem;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.15rem;
    }
    .payment-modal .account-detail dd {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--bk-text-dark, #2c2c2c);
        margin: 0;
    }
    .btn-payment {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        border: 2px solid var(--bk-primary);
        background: #fff;
        color: var(--bk-primary);
        transition: all 0.2s;
    }
    .btn-payment:hover {
        background: var(--bk-primary);
        color: #fff;
    }
    .btn-payment.filled {
        background: var(--bk-primary);
        color: #fff;
    }
    .btn-payment.filled:hover {
        background: var(--bk-primary-dark);
        border-color: var(--bk-primary-dark);
    }
    .upload-area {
        border: 2px dashed #e0d4c3;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .upload-area:hover {
        border-color: var(--bk-primary);
        background: #fdf6ee;
    }
    .upload-area input[type="file"] {
        display: none;
    }
    .upload-area__icon {
        font-size: 2rem;
        color: var(--bk-primary);
        margin-bottom: 0.5rem;
    }
    .upload-area__label {
        font-size: 0.9rem;
        color: #888;
    }
    .upload-area__label span {
        color: var(--bk-primary);
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
    }
    #upload-preview {
        display: none;
        margin-top: 1rem;
        border-radius: 8px;
        overflow: hidden;
        text-align: center;
    }
    #upload-preview img {
        max-height: 180px;
        max-width: 100%;
        border-radius: 8px;
        object-fit: contain;
    }
    #preview-pdf {
        display: none;
        width: 100%;
        height: 320px;
        border: 1.5px solid #e0d4c3;
        border-radius: 8px;
    }
    .upload-preview__footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }
    .upload-preview__footer #upload-filename {
        margin: 0;
        font-size: 0.82rem;
        color: #888;
        word-break: break-all;
    }
    #btn-remove-upload {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: none;
        background: none;
        color: #d9534f;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        white-space: nowrap;
        transition: background 0.2s;
    }
    #btn-remove-upload:hover {
        background: rgba(217,83,79,0.1);
    }
    .order-summary-card {
        background: #fdf6ee;
        border-radius: 12px;
        border: 1px solid #f0e6d6;
        padding: 1.5rem;
        position: sticky;
        top: 100px;
    }
    .order-summary-card h5 {
        color: var(--bk-primary);
        font-weight: 700;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e8d9c4;
    }
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #ece1d0;
        font-size: 0.9rem;
    }
    .order-item:last-of-type {
        border-bottom: none;
    }
    .order-item__name {
        font-weight: 500;
        color: var(--bk-text-dark, #2c2c2c);
        flex: 1;
    }
    .order-item__qty {
        color: #888;
        font-size: 0.8rem;
    }
    .order-item__price {
        font-weight: 600;
        color: var(--bk-primary);
        white-space: nowrap;
    }
    .order-total-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        font-size: 0.925rem;
    }
    .order-total-row.grand {
        border-top: 2px solid #e8d9c4;
        margin-top: 0.5rem;
        padding-top: 1rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--bk-primary);
    }
    #qr-modal .payment-modal {
        max-width: 460px;
    }
    .qr-placeholder {
        width: 260px;
        height: 260px;
        border: 2px dashed #e0d4c3;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        background: #fdf6ee;
        color: #bbb;
        font-size: 0.85rem;
    }
    .qr-placeholder img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container">
        <h2 class="page-title">Shipping & Checkout</h2>

        {{-- Checkout Steps --}}
        <div class="checkout-steps">
            <a href="{{ route('cart.index') }}" class="checkout-steps__item">
                <span class="checkout-steps__item-number">01</span>
                <span class="checkout-steps__item-title">
                    <span>Shopping Bag</span>
                    <em>Manage Your Items List</em>
                </span>
            </a>
            <a href="javascript:void(0)" class="checkout-steps__item active">
                <span class="checkout-steps__item-number">02</span>
                <span class="checkout-steps__item-title">
                    <span>Shipping and Checkout</span>
                    <em>Checkout Your Items List</em>
                </span>
            </a>
            <a href="javascript:void(0)" class="checkout-steps__item">
                <span class="checkout-steps__item-number">03</span>
                <span class="checkout-steps__item-title">
                    <span>Confirmation</span>
                    <em>Review And Submit Your Order</em>
                </span>
            </a>
        </div>

        <form action="{{ route('cart.place-order') }}" method="POST" enctype="multipart/form-data" id="checkout-form">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                {{-- Left Column: Form Fields --}}
                <div class="col-lg-7 col-xl-8">

                    {{-- Contact & Shipping Details --}}
                    <div class="checkout-form-card">
                        <h5>Contact & Shipping Details</h5>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="checkout-form-label" for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name"
                                    class="checkout-form-control"
                                    placeholder="e.g. Wan Amirul Nadzmin"
                                    value="{{ old('full_name', auth()->user()->name ?? '') }}"
                                    required>
                                <div class="field-error" id="error-full_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="checkout-form-label" for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                    class="checkout-form-control"
                                    placeholder="e.g. 0123456789"
                                    inputmode="numeric"
                                    maxlength="11"
                                    value="{{ old('phone') }}"
                                    required>
                                <div class="field-error" id="error-phone"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="checkout-form-label" for="email">Email Address</label>
                                <input type="email" id="email" name="email"
                                    class="checkout-form-control"
                                    placeholder="e.g. you@example.com"
                                    value="{{ old('email', auth()->user()->email ?? '') }}"
                                    required>
                                <div class="field-error" id="error-email"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="checkout-form-label" for="fulfillment_type">Order Type</label>
                                <select id="fulfillment_type" name="fulfillment_type"
                                    class="checkout-form-control" required>
                                    <option value="" disabled selected>Select pickup or delivery</option>
                                    <option value="pickup" {{ old('fulfillment_type') == 'pickup' ? 'selected' : '' }}>
                                        Pickup
                                    </option>
                                    <option value="delivery" {{ old('fulfillment_type') == 'delivery' ? 'selected' : '' }}>
                                        Delivery
                                    </option>
                                </select>
                                <div class="field-error" id="error-fulfillment_type"></div>
                            </div>

                            @php
                                $today = \Carbon\Carbon::today();
                                $minDate = $today->toDateString();
                                $maxDate = $today->copy()->addMonthNoOverflow()->endOfMonth()->toDateString();
                            @endphp

                            <div class="col-md-6">
                                <label class="checkout-form-label" for="pickup_date">Date</label>
                                <input type="date" id="pickup_date" name="pickup_date"
                                    class="checkout-form-control"
                                    min="{{ $minDate }}"
                                    max="{{ $maxDate }}"
                                    value="{{ old('pickup_date') }}"
                                    required>
                                <div class="field-error" id="error-pickup_date"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="checkout-form-label" for="pickup_time">Time</label>
                                <select id="pickup_time" name="pickup_time"
                                    class="checkout-form-control" required>
                                    <option value="" disabled {{ old('pickup_time') ? '' : 'selected' }}>Select a time slot</option>
                                    @for ($minutes = 8 * 60; $minutes <= 22 * 60; $minutes += 30)
                                        @php $slot = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60); @endphp
                                        <option value="{{ $slot }}" {{ old('pickup_time') == $slot ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('g:i A') }}
                                        </option>
                                    @endfor
                                </select>
                                <div class="field-error" id="error-pickup_time"></div>
                            </div>

                            {{-- Conditional delivery address --}}
                            <div class="col-12" id="address-section">
                                <label class="checkout-form-label" for="address-input">Delivery Address</label>
                                <div class="address-wrap">
                                    <input type="text" id="address-input"
                                        class="checkout-form-control"
                                        placeholder="Start typing your address..."
                                        autocomplete="off"
                                        value="{{ old('address') }}">
                                    <div id="address-suggestions" class="address-suggestions"></div>
                                </div>
                                <input type="hidden" id="address" name="address" value="{{ old('address') }}">
                                <div class="field-error" id="error-address"></div>
                                <div id="address-map-box">
                                    <div id="address-map"></div>
                                    <div id="address-map-footer">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span id="address-map-text">Delivery location pinned</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Section --}}
                    <div class="checkout-form-card">
                        <h5>Payment</h5>

                        <p class="text-muted mb-3" style="font-size:0.9rem;">
                            Please make your payment via the details below, then upload your proof of payment.
                        </p>

                        {{-- QR & Account buttons --}}
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <button type="button" class="btn-payment filled" id="btn-show-qr">
                                <i class="bi bi-qr-code"></i>
                                Show QR Code
                            </button>
                            <button type="button" class="btn-payment" id="btn-show-account">
                                <i class="bi bi-bank2"></i>
                                Show Account Number
                            </button>
                        </div>

                        {{-- Proof of payment upload --}}
                        <label class="checkout-form-label">Proof of Payment</label>
                        <div class="upload-area" id="upload-area" onclick="document.getElementById('proof_of_payment').click()">
                            <input type="file" id="proof_of_payment" name="proof_of_payment"
                                accept="image/*,.pdf" onchange="handleUploadPreview(this)">
                            <div class="upload-area__icon">
                                <i class="bi bi-cloud-upload"></i>
                            </div>
                            <div class="upload-area__label">
                                <span>Click to upload</span> or drag & drop your payment screenshot here
                            </div>
                        </div>
                        <div id="upload-preview">
                            <img id="preview-img" src="" alt="Payment proof preview">
                            <iframe id="preview-pdf" src="" title="Payment proof preview"></iframe>
                            <div class="upload-preview__footer">
                                <p id="upload-filename"></p>
                                <button type="button" id="btn-remove-upload" title="Remove file">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        <div class="field-error" id="error-proof_of_payment"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary px-4">
                            Back to Cart
                        </a>
                        <button type="submit" class="btn btn-primary btn-checkout px-5">
                            Place Order
                        </button>
                    </div>
                </div>

                {{-- Right Column: Order Summary --}}
                <div class="col-lg-5 col-xl-4">
                    <div class="order-summary-card">
                        <h5>Order Summary</h5>

                        <div class="order-items mb-3">
                            @foreach ($items as $item)
                            <div class="order-item">
                                <div class="order-item__name">
                                    {{ $item->name }}
                                    <div class="order-item__qty">Qty: {{ $item->qty }}</div>
                                </div>
                                <div class="order-item__price">RM {{ number_format($item->subtotal(), 2) }}</div>
                            </div>
                            @endforeach
                        </div>

                        <div class="order-total-row">
                            <span>Subtotal</span>
                            <span>RM {{ Cart::instance('cart')->subtotal() }}</span>
                        </div>
                        {{-- Spend-based delivery reward tiers — disabled for now.
                        @php
                            $checkoutSubtotal = (float) Cart::instance('cart')->subtotal(2, '.', '');
                            $checkoutReward = \App\Support\DeliveryReward::forSubtotal($checkoutSubtotal);
                        @endphp
                        <div class="order-total-row" style="align-items:flex-start;">
                            <span>Delivery</span>
                            <span id="summary-delivery"
                                  data-reward-label="{{ $checkoutReward['label'] }}"
                                  data-has-reward="{{ $checkoutReward['has_reward'] ? '1' : '0' }}"
                                  style="text-align:right;{{ $checkoutReward['has_reward'] ? 'color:var(--bk-success);font-weight:600;' : 'font-size:0.8rem;color:var(--bk-text-medium);' }}">
                                {{ $checkoutReward['label'] }}
                            </span>
                        </div>
                        --}}
                        <div class="order-total-row" id="summary-delivery-row" style="align-items:flex-start;display:none;">
                            <span>Delivery</span>
                            <span id="summary-delivery" style="text-align:right;font-size:0.8rem;color:var(--bk-text-medium);">
                                Delivery fee is based on GrabExpress rate
                            </span>
                        </div>
                        <div class="order-total-row grand">
                            <span>Total</span>
                            <span id="summary-total" data-subtotal="{{ Cart::instance('cart')->subtotal() }}">RM {{ Cart::instance('cart')->subtotal() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</main>

{{-- QR Code Modal --}}
<div class="payment-modal-backdrop" id="qr-modal">
    <div class="payment-modal">
        <button class="payment-modal__close" onclick="closeModal('qr-modal')">&times;</button>
        <h6>Scan to Pay</h6>
        <div class="qr-placeholder">
            <img src="{{ asset('assets/images/qr.png') }}" alt="Payment QR Code">
        </div>
        <p style="font-size:0.85rem;color:#888;">
            Scan this QR code using your banking app to make payment.
        </p>
    </div>
</div>

{{-- Account Number Modal --}}
<div class="payment-modal-backdrop" id="account-modal">
    <div class="payment-modal">
        <button class="payment-modal__close" onclick="closeModal('account-modal')">&times;</button>
        <h6>Bank Transfer Details</h6>
        <dl class="account-detail">
            <dt>Bank</dt>
            <dd>Maybank</dd>
        </dl>
        <dl class="account-detail">
            <dt>Account Name</dt>
            <dd>Rohanizah Binti Abdullah</dd>
        </dl>
        <dl class="account-detail">
            <dt>Account Number</dt>
            <dd>1625 0826 7241</dd>
        </dl>
        <p style="font-size:0.82rem;color:#aaa;margin-top:0.75rem;">
            After transfer, upload your payment proof above.
        </p>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Show/hide address section based on fulfillment type
    const fulfillmentSelect = document.getElementById('fulfillment_type');
    const addressSection = document.getElementById('address-section');
    const addressField = document.getElementById('address');        // hidden input submitted to form
    const addressInput = document.getElementById('address-input');  // visible text input
    const summaryDeliveryRow = document.getElementById('summary-delivery-row');
    const summaryTotal = document.getElementById('summary-total');
    const orderSubtotal = summaryTotal.dataset.subtotal;

    function showDeliveryReward() {
        // Delivery selected: delivery fee is charged at live GrabExpress rates,
        // so total = subtotal + delivery fee (fee shown at payment, not computed here).
        summaryDeliveryRow.style.display = 'flex';
        summaryTotal.textContent = 'RM ' + orderSubtotal + ' + Delivery Fee';
    }

    function showPickupDelivery() {
        // Pickup: no delivery fee, total is just the subtotal.
        summaryDeliveryRow.style.display = 'none';
        summaryTotal.textContent = 'RM ' + orderSubtotal;
    }

    // ── Address autocomplete (Nominatim / OpenStreetMap) + Leaflet map ──
    (function () {
        const input     = document.getElementById('address-input');
        const hidden    = document.getElementById('address');
        const dropdown  = document.getElementById('address-suggestions');
        const mapBox    = document.getElementById('address-map-box');
        const mapText   = document.getElementById('address-map-text');

        let timer = null, highlightIdx = -1, results = [];
        let leafMap = null, leafMarker = null;

        // ── Step A: helpers ──────────────────────────────────────────────

        function setStatus(msg) {
            dropdown.innerHTML = '<div class="address-suggestion-status">' + msg + '</div>';
            dropdown.classList.add('open');
        }

        function closeDropdown() {
            dropdown.classList.remove('open');
            dropdown.innerHTML = '';
            highlightIdx = -1;
            results = [];
        }

        function renderResults(places) {
            results = places;
            highlightIdx = -1;
            dropdown.innerHTML = '';
            if (!places.length) { setStatus('No results found'); return; }
            places.forEach(function (p, i) {
                const el = document.createElement('div');
                el.className = 'address-suggestion-item';
                el.textContent = p.display_name;
                el.addEventListener('mousedown', function (e) { e.preventDefault(); pick(i); });
                dropdown.appendChild(el);
            });
            dropdown.classList.add('open');
        }

        function pick(i) {
            const p = results[i];
            input.value  = p.display_name;
            hidden.value = p.display_name;
            closeDropdown();
            showMap(parseFloat(p.lat), parseFloat(p.lon), p.display_name);
        }

        // ── Step B: show/update the Leaflet map ─────────────────────────

        function showMap(lat, lng, label) {
            mapText.textContent = label;
            mapBox.classList.add('show');

            // Defer map init by one tick so the browser paints display:block
            // before Leaflet reads the container dimensions.
            setTimeout(function () {
                if (!leafMap) {
                    leafMap = L.map('address-map', { scrollWheelZoom: false });
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19
                    }).addTo(leafMap);
                }

                // invalidateSize BEFORE setView so the view is placed correctly
                leafMap.invalidateSize();
                leafMap.setView([lat, lng], 16);

                if (leafMarker) {
                    leafMarker.setLatLng([lat, lng]);
                } else {
                    leafMarker = L.marker([lat, lng]).addTo(leafMap);
                }
            }, 50);
        }

        // ── Step C: reverse geocode lat/lng → human address ─────────────
        // Nominatim /reverse returns { display_name: "...", lat, lon }

        function reverseGeocode(lat, lng) {
            fetch(
                'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng,
                { headers: { 'Accept-Language': 'en' } }
            )
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const addr = data.display_name || '';
                input.value  = addr;
                hidden.value = addr;
                mapText.textContent = addr;
            })
            .catch(function () {
                mapText.textContent = 'Could not resolve address.';
            });
        }

        // ── Step D: detect current location and pin on map ──────────────
        // Called when user switches to "Delivery". Shows map immediately
        // and asks the browser for GPS. On success: pin + auto-fill address.
        // On failure: map stays open, user types manually.

        function detectLocation() {
            // Show map box right away at a default Malaysia centre
            mapBox.classList.add('show');
            mapText.textContent = 'Detecting your location…';

            if (!navigator.geolocation) {
                mapText.textContent = 'Geolocation not supported. Please type your address below.';
                showMap(3.8077, 103.4260, ''); // Malaysia centre fallback
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    // SUCCESS — browser gave us coordinates
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    showMap(lat, lng, 'Locating address…');
                    reverseGeocode(lat, lng);
                },
                function (err) {
                    // DENIED or ERROR — user must type manually
                    mapText.textContent = 'Location denied. Please type your address below.';
                    showMap(3.8077, 103.4260, ''); // Malaysia centre fallback
                },
                { timeout: 8000, maximumAge: 60000 }
            );
        }

        // ── Step E: keyboard navigation ──────────────────────────────────

        function highlight(idx) {
            const items = dropdown.querySelectorAll('.address-suggestion-item');
            items.forEach(function (el) { el.classList.remove('active'); });
            if (idx >= 0 && idx < items.length) {
                items[idx].classList.add('active');
                highlightIdx = idx;
            }
        }

        // ── Step F: manual typing still works (override detected address) ─
        // When user types, run autocomplete search as before.
        // Map hides until they pick a suggestion.

        input.addEventListener('input', function () {
            const q = this.value.trim();
            hidden.value = q;
            clearTimeout(timer);
            if (q.length < 3) { closeDropdown(); return; }
            setStatus('Searching…');
            timer = setTimeout(function () {
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=my&q=' + encodeURIComponent(q), {
                    headers: { 'Accept-Language': 'en' }
                })
                .then(function (r) { return r.json(); })
                .then(renderResults)
                .catch(function () { setStatus('Search failed. Try again.'); });
            }, 400);
        });

        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.address-suggestion-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown')  { e.preventDefault(); highlight(Math.min(highlightIdx + 1, items.length - 1)); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(Math.max(highlightIdx - 1, 0)); }
            else if (e.key === 'Enter' && highlightIdx >= 0) { e.preventDefault(); pick(highlightIdx); }
            else if (e.key === 'Escape') { closeDropdown(); }
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                closeDropdown();
                if (!hidden.value) hidden.value = input.value.trim();
            }, 160);
        });

        // Expose detectLocation so the fulfillment change handler can call it
        window._detectDeliveryLocation = detectLocation;
    })();

    // Block keyboard entry on the date field — typed digits can produce a
    // date outside min/max (or a malformed value) that fails on submit.
    // Tab is allowed through so keyboard users can still move past the field;
    // picking a date via the calendar picker (mouse) is unaffected.
    document.getElementById('pickup_date').addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') {
            e.preventDefault();
        }
    });

    // ── Fulfillment type toggle ──────────────────────────────────────────

    fulfillmentSelect.addEventListener('change', function () {
        if (this.value === 'delivery') {
            addressSection.style.display = 'block';
            addressInput.required = true;
            showDeliveryReward();
            // NEW: immediately show map and try to pin current location
            window._detectDeliveryLocation();
        } else {
            addressSection.style.display = 'none';
            addressInput.required = false;
            showPickupDelivery();
        }
    });

    // Restore on page load if delivery was already selected (e.g. form validation error)
    if (fulfillmentSelect.value === 'delivery') {
        addressSection.style.display = 'block';
        addressInput.required = true;
        showDeliveryReward();
        // Only auto-detect if no address was already filled in (fresh page vs. re-render)
        if (!document.getElementById('address').value) {
            window._detectDeliveryLocation();
        }
    } else if (fulfillmentSelect.value === 'pickup') {
        showPickupDelivery();
    }

    // Modal helpers
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    document.getElementById('btn-show-qr').addEventListener('click', () => openModal('qr-modal'));
    document.getElementById('btn-show-account').addEventListener('click', () => openModal('account-modal'));

    // Close modals when clicking backdrop
    document.querySelectorAll('.payment-modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    // Upload preview
    let currentPreviewURL = null;

    function handleUploadPreview(input) {
        const preview = document.getElementById('upload-preview');
        const previewImg = document.getElementById('preview-img');
        const previewPdf = document.getElementById('preview-pdf');
        const filename = document.getElementById('upload-filename');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            filename.textContent = file.name;

            // Reset both previews before showing the relevant one
            previewImg.style.display = 'none';
            previewPdf.style.display = 'none';

            if (currentPreviewURL) URL.revokeObjectURL(currentPreviewURL);
            currentPreviewURL = URL.createObjectURL(file);

            if (file.type.startsWith('image/')) {
                previewImg.src = currentPreviewURL;
                previewImg.style.display = 'block';
            } else if (file.type === 'application/pdf') {
                previewPdf.src = currentPreviewURL;
                previewPdf.style.display = 'block';
            }

            preview.style.display = 'block';
        }
    }

    // Remove uploaded proof of payment
    document.getElementById('btn-remove-upload').addEventListener('click', function (e) {
        e.stopPropagation(); // don't trigger the upload-area click (re-opens file picker)

        const fileInput = document.getElementById('proof_of_payment');
        const preview = document.getElementById('upload-preview');
        const previewImg = document.getElementById('preview-img');
        const previewPdf = document.getElementById('preview-pdf');
        const filename = document.getElementById('upload-filename');

        fileInput.value = '';
        if (currentPreviewURL) {
            URL.revokeObjectURL(currentPreviewURL);
            currentPreviewURL = null;
        }
        previewImg.src = '';
        previewImg.style.display = 'none';
        previewPdf.src = '';
        previewPdf.style.display = 'none';
        filename.textContent = '';
        preview.style.display = 'none';
    });

    // ── Front-end validation ─────────────────────────────────────────────
    (function () {
        const form = document.getElementById('checkout-form');

        const fields = {
            full_name: {
                el: document.getElementById('full_name'),
                validate: function (v) {
                    v = v.trim();
                    if (!v) return 'Please enter your full name.';
                    if (!/^[\p{L}\s'\-.]+$/u.test(v)) return 'Name can only contain letters, spaces, apostrophes, hyphens and periods.';
                    return null;
                }
            },
            phone: {
                el: document.getElementById('phone'),
                validate: function (v) {
                    v = v.trim();
                    if (!v) return 'Please enter your phone number.';
                    if (!/^\d+$/.test(v)) return 'Phone number can only contain digits.';
                    if (v.length < 10 || v.length > 11) return 'Phone number must be 10 to 11 digits.';
                    return null;
                }
            },
            email: {
                el: document.getElementById('email'),
                validate: function (v) {
                    v = v.trim();
                    if (!v) return 'Please enter your email address.';
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return 'Please enter a valid email address.';
                    return null;
                }
            },
            fulfillment_type: {
                el: document.getElementById('fulfillment_type'),
                validate: function (v) {
                    if (!v) return 'Please select pickup or delivery.';
                    return null;
                }
            },
            pickup_date: {
                el: document.getElementById('pickup_date'),
                validate: function (v) {
                    if (!v) return 'Please select a pickup/delivery date.';
                    const el = fields.pickup_date.el;
                    if (el.min && v < el.min) return 'Selected date is too early.';
                    if (el.max && v > el.max) return 'Selected date is too far ahead.';
                    return null;
                }
            },
            pickup_time: {
                el: document.getElementById('pickup_time'),
                validate: function (v) {
                    if (!v) return 'Please select a time slot.';
                    return null;
                }
            },
            address: {
                el: document.getElementById('address-input'),
                validate: function (v) {
                    if (fulfillmentSelect.value !== 'delivery') return null;
                    if (!v.trim()) return 'Please enter your delivery address.';
                    return null;
                }
            },
            proof_of_payment: {
                el: document.getElementById('proof_of_payment'),
                validate: function () {
                    const files = fields.proof_of_payment.el.files;
                    if (!files || !files.length) return 'Please upload your proof of payment.';
                    return null;
                }
            }
        };

        function showError(name, message) {
            const errorEl = document.getElementById('error-' + name);
            const inputEl = fields[name].el;
            if (message) {
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.add('show');
                }
                inputEl.classList.add('is-invalid');
            } else {
                if (errorEl) {
                    errorEl.textContent = '';
                    errorEl.classList.remove('show');
                }
                inputEl.classList.remove('is-invalid');
            }
        }

        function validateField(name) {
            const value = fields[name].el.value;
            const message = fields[name].validate(value);
            showError(name, message);
            return !message;
        }

        function validateAll() {
            let firstInvalid = null;
            Object.keys(fields).forEach(function (name) {
                const valid = validateField(name);
                if (!valid && !firstInvalid) firstInvalid = fields[name].el;
            });
            return firstInvalid;
        }

        // Restrict phone input to digits only, max 11 digits
        fields.phone.el.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 11);
        });

        // Real-time validation as the user interacts with each field
        Object.keys(fields).forEach(function (name) {
            const el = fields[name].el;
            el.addEventListener('blur', function () { validateField(name); });
            el.addEventListener('input', function () { validateField(name); });
            el.addEventListener('change', function () { validateField(name); });
        });

        // Re-validate address whenever fulfillment type changes
        fulfillmentSelect.addEventListener('change', function () {
            showError('address', null);
        });

        form.addEventListener('submit', function (e) {
            const firstInvalid = validateAll();
            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        });
    })();
</script>
@endpush
@endsection
