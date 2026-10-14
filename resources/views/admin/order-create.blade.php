@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Create Order</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li><a href="{{ route('admin.orders') }}"><div class="text-tiny">Orders</div></a></li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li><div class="text-tiny">Create Order</div></li>
                </ul>
            </div>

            <form action="{{ route('admin.order.store') }}" method="POST" id="createOrderForm">
                @csrf
                <div class="oc-grid">

                    <!-- Left Column: Customer + Delivery + Notes -->
                    <div class="oc-col-main">

                        <!-- Customer Information -->
                        <div class="wg-box mb-24">
                            <div class="title-box">
                                <i class="icon-user"></i>
                                <div class="body-title">Customer Information</div>
                            </div>

                            <fieldset>
                                <div class="body-title mb-10">Name <span class="tf-color-1">*</span></div>
                                <input type="text" name="name" placeholder="Customer full name"
                                       value="{{ old('name') }}" required>
                            </fieldset>
                            @error('name') <div class="oc-error">{{ $message }}</div> @enderror

                            <div class="cols gap22">
                                <fieldset>
                                    <div class="body-title mb-10">Phone <span class="tf-color-1">*</span></div>
                                    <input type="text" name="phone" placeholder="e.g. 011-23456789"
                                           value="{{ old('phone') }}" required>
                                </fieldset>
                                <fieldset>
                                    <div class="body-title mb-10">Status <span class="tf-color-1">*</span></div>
                                    <div class="select">
                                        <select name="status" required>
                                            @foreach($statuses as $key => $label)
                                                <option value="{{ $key }}" {{ old('status', 'pending_payment') === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </fieldset>
                            </div>
                            @error('phone') <div class="oc-error">{{ $message }}</div> @enderror
                            @error('status') <div class="oc-error">{{ $message }}</div> @enderror

                            <fieldset>
                                <div class="body-title mb-10">Delivery Address <span class="tf-color-1">*</span></div>
                                <textarea name="address" rows="3" placeholder="Full delivery address" required>{{ old('address') }}</textarea>
                            </fieldset>
                            @error('address') <div class="oc-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Delivery -->
                        <div class="wg-box mb-24">
                            <div class="title-box">
                                <i class="icon-truck"></i>
                                <div class="body-title">Delivery</div>
                            </div>

                            <div class="oc-switch-row">
                                <label class="bk-switch">
                                    <input type="checkbox" id="hasDeliveryToggle" class="bk-switch__input"
                                           {{ old('has_delivery') === 'yes' ? 'checked' : '' }}
                                           onchange="toggleDeliveryCharge(this)">
                                    <span class="bk-switch__slider"></span>
                                </label>
                                <span class="body-title" id="hasDeliveryLabel">
                                    {{ old('has_delivery') === 'yes' ? 'Delivery required' : 'No delivery (self-collect)' }}
                                </span>
                            </div>
                            <input type="hidden" name="has_delivery" id="hasDeliveryInput" value="{{ old('has_delivery', 'no') }}">
                            @error('has_delivery') <div class="oc-error">{{ $message }}</div> @enderror

                            <fieldset id="deliveryChargeField" style="{{ old('has_delivery') === 'yes' ? '' : 'display:none;' }}">
                                <div class="body-title mb-10">Delivery Charge (RM) <span class="tf-color-1">*</span></div>
                                <input type="number" name="delivery_charge" id="deliveryChargeInput"
                                       value="{{ old('delivery_charge', '0.00') }}"
                                       min="0" step="0.01" placeholder="e.g. 5.00">
                            </fieldset>
                            @error('delivery_charge') <div class="oc-error">{{ $message }}</div> @enderror
                        </div>

                        <!-- Notes -->
                        <div class="wg-box">
                            <div class="title-box">
                                <i class="icon-file-text"></i>
                                <div class="body-title">Order Notes</div>
                            </div>
                            <fieldset>
                                <textarea name="notes" rows="3" placeholder="Any special instructions or notes (optional)">{{ old('notes') }}</textarea>
                            </fieldset>
                            @error('notes') <div class="oc-error">{{ $message }}</div> @enderror
                        </div>

                    </div>

                    <!-- Right Column: Order Items + Summary -->
                    <div class="oc-col-side">

                        <!-- Order Items -->
                        <div class="wg-box mb-24">
                            <div class="flex items-center justify-between">
                                <div class="title-box">
                                    <i class="icon-shopping-bag"></i>
                                    <div class="body-title">Order Items</div>
                                </div>
                                <button type="button" class="oc-add-item-btn" onclick="addItemRow()">
                                    <i class="icon-plus"></i> Add Item
                                </button>
                            </div>

                            @error('items') <div class="oc-error">{{ $message }}</div> @enderror

                            <div id="itemsContainer" class="oc-items-container">
                                <!-- Item Row Template -->
                                <div class="order-item-row" data-index="0">
                                    <div class="order-item-row__head">
                                        <span class="text-tiny fw-600">Item #<span class="item-num">1</span></span>
                                        <button type="button" class="remove-item-btn" onclick="removeItemRow(this)" style="display:none;">
                                            <i class="icon-trash-2"></i>
                                        </button>
                                    </div>
                                    <div class="select">
                                        <select name="items[0][product_id]" class="product-select select2-product" required>
                                            <option value="">-- Search product --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-price="{{ $product->sale_price > 0 ? $product->sale_price : $product->regular_price }}">
                                                    {{ $product->name }}
                                                    (RM {{ number_format($product->sale_price > 0 ? $product->sale_price : $product->regular_price, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="cols gap12 mt-12">
                                        <fieldset>
                                            <div class="body-title text-tiny mb-6">Price (RM)</div>
                                            <input type="text" class="item-price" readonly placeholder="0.00">
                                        </fieldset>
                                        <fieldset>
                                            <div class="body-title text-tiny mb-6">Qty <span class="tf-color-1">*</span></div>
                                            <input type="number" name="items[0][quantity]" class="item-qty"
                                                   value="{{ old('items.0.quantity', 1) }}" min="1" required onchange="recalculate()">
                                        </fieldset>
                                    </div>
                                    <div class="order-item-row__total">
                                        Line total: <strong class="item-line-total">RM 0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="wg-box mb-24 oc-summary">
                            <div class="title-box">
                                <i class="icon-clipboard"></i>
                                <div class="body-title">Order Summary</div>
                            </div>
                            <div class="oc-summary-row">
                                <span>Subtotal</span>
                                <span id="summarySubtotal">RM 0.00</span>
                            </div>
                            <div class="oc-summary-row">
                                <span>Delivery</span>
                                <span id="summaryDelivery">RM 0.00</span>
                            </div>
                            <div class="oc-summary-row oc-summary-row--total">
                                <span>Total</span>
                                <span id="summaryTotal">RM 0.00</span>
                            </div>
                        </div>

                        <div class="flex gap10">
                            <button type="submit" class="tf-button w-full">
                                <i class="icon-check"></i> Create Order
                            </button>
                            <a href="{{ route('admin.orders') }}" class="tf-button style-2">Cancel</a>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .title-box i { color: #BE6A36; }

    .mb-24 { margin-bottom: 24px; }
    .mb-6 { margin-bottom: 6px; }
    .mt-12 { margin-top: 12px; }
    .gap12 { gap: 12px; }
    .w-full { width: 100%; }
    .fw-600 { font-weight: 600; }

    /* ── Two-column layout ─────────────────────────────────── */
    .oc-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        align-items: start;
        gap: 24px;
    }
    @media (max-width: 992px) {
        .oc-grid { grid-template-columns: 1fr; }
    }

    /* ── Inline field errors ───────────────────────────────── */
    .oc-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: -12px;
        margin-bottom: 4px;
    }

    /* ── Delivery toggle row ───────────────────────────────── */
    .oc-switch-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* ── Add item button ───────────────────────────────────── */
    .oc-add-item-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--Input);
        background: transparent;
        color: #BE6A36;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .oc-add-item-btn:hover {
        background: #BE6A36;
        border-color: #BE6A36;
        color: #fff;
    }

    /* ── Order item rows ───────────────────────────────────── */
    .oc-items-container {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .order-item-row {
        border: 1px solid var(--Input);
        border-radius: 12px;
        padding: 16px;
        background: var(--backdrop, #F8FAFC);
        position: relative;
    }
    .order-item-row__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        color: var(--Heading);
    }
    .remove-item-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .remove-item-btn:hover { background: rgba(220, 53, 69, 0.2); }
    .order-item-row .item-price[readonly] {
        background: var(--Input);
        color: var(--Body-Text);
        cursor: not-allowed;
    }
    .order-item-row__total {
        text-align: right;
        font-size: 13px;
        color: var(--Body-Text);
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed var(--Input);
    }
    .order-item-row__total strong { color: var(--Heading); }

    /* ── Order summary ─────────────────────────────────────── */
    .oc-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        color: var(--Body-Text);
        padding: 6px 0;
    }
    .oc-summary-row--total {
        margin-top: 6px;
        padding-top: 14px;
        border-top: 1px solid var(--Input);
        font-size: 16px;
        font-weight: 700;
        color: var(--Heading);
    }

    /* ── Select2 blended into theme ────────────────────────── */
    .select2-container--default .select2-selection--single {
        height: 50px;
        border: 1px solid var(--Input);
        border-radius: 12px;
        background-color: transparent;
        display: flex;
        align-items: center;
        padding: 0 22px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 0;
        line-height: normal;
        color: var(--Heading);
        font-size: 14px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
        right: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--Note);
    }
    .select2-dropdown {
        border-radius: 12px;
        border: 1px solid var(--Input);
        overflow: hidden;
    }
    .select2-container--open .select2-dropdown--below {
        margin-top: 6px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Product data from server
    const products = @json($products->keyBy('id'));
    let itemIndex = 1;

    function buildProductOptions() {
        let options = '<option value="">-- Select Product --</option>';
        for (const id in products) {
            const p = products[id];
            const price = p.sale_price > 0 ? p.sale_price : p.regular_price;
            options += `<option value="${p.id}" data-price="${price}">${p.name} (RM ${parseFloat(price).toFixed(2)})</option>`;
        }
        return options;
    }

    function initSelect2(selectEl) {
        $(selectEl).select2({
            placeholder: '-- Search product --',
            allowClear: true,
            width: '100%',
            dropdownParent: $(selectEl).closest('.order-item-row'),
        }).on('change', function () {
            fillPrice(this);
        });
    }

    function addItemRow() {
        const idx = itemIndex++;
        const container = document.getElementById('itemsContainer');

        const row = document.createElement('div');
        row.className = 'order-item-row';
        row.setAttribute('data-index', idx);
        row.innerHTML = `
            <div class="order-item-row__head">
                <span class="text-tiny fw-600">Item #<span class="item-num">${idx + 1}</span></span>
                <button type="button" class="remove-item-btn" onclick="removeItemRow(this)">
                    <i class="icon-trash-2"></i>
                </button>
            </div>
            <div class="select">
                <select name="items[${idx}][product_id]" class="product-select select2-product" required>
                    ${buildProductOptions()}
                </select>
            </div>
            <div class="cols gap12 mt-12">
                <fieldset>
                    <div class="body-title text-tiny mb-6">Price (RM)</div>
                    <input type="text" class="item-price" readonly placeholder="0.00">
                </fieldset>
                <fieldset>
                    <div class="body-title text-tiny mb-6">Qty <span class="tf-color-1">*</span></div>
                    <input type="number" name="items[${idx}][quantity]" class="item-qty"
                           value="1" min="1" required onchange="recalculate()">
                </fieldset>
            </div>
            <div class="order-item-row__total">
                Line total: <strong class="item-line-total">RM 0.00</strong>
            </div>
        `;
        container.appendChild(row);
        initSelect2(row.querySelector('.select2-product'));
        updateRemoveButtons();
        recalculate();
    }

    function removeItemRow(btn) {
        const row = btn.closest('.order-item-row');
        $(row.querySelector('.select2-product')).select2('destroy');
        row.remove();
        reindexRows();
        updateRemoveButtons();
        recalculate();
    }

    function reindexRows() {
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach((row, i) => {
            row.querySelector('.item-num').textContent = i + 1;
            const sel = row.querySelector('.product-select');
            // Destroy, rename, re-init select2
            $(sel).select2('destroy');
            sel.name = `items[${i}][product_id]`;
            initSelect2(sel);
            row.querySelector('.item-qty').name = `items[${i}][quantity]`;
        });
        itemIndex = rows.length;
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-item-btn');
            if (btn) btn.style.display = rows.length > 1 ? 'inline-flex' : 'none';
        });
    }

    function fillPrice(select) {
        const row = select.closest('.order-item-row');
        const priceInput = row.querySelector('.item-price');
        const selected = select.options[select.selectedIndex];
        const price = selected ? (parseFloat(selected.getAttribute('data-price')) || 0) : 0;
        priceInput.value = price > 0 ? price.toFixed(2) : '';
        recalculate();
    }

    function recalculate() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const priceInput = row.querySelector('.item-price');
            const qtyInput = row.querySelector('.item-qty');
            const lineTotal = row.querySelector('.item-line-total');
            const price = parseFloat(priceInput.value) || 0;
            const qty = parseInt(qtyInput.value) || 0;
            const line = price * qty;
            subtotal += line;
            lineTotal.textContent = 'RM ' + line.toFixed(2);
        });

        const delivering = document.getElementById('hasDeliveryInput').value === 'yes';
        let delivery = 0;
        if (delivering) {
            delivery = parseFloat(document.getElementById('deliveryChargeInput').value) || 0;
        }

        document.getElementById('summarySubtotal').textContent = 'RM ' + subtotal.toFixed(2);
        document.getElementById('summaryDelivery').textContent = 'RM ' + delivery.toFixed(2);
        document.getElementById('summaryTotal').textContent = 'RM ' + (subtotal + delivery).toFixed(2);
    }

    function toggleDeliveryCharge(checkbox) {
        const field = document.getElementById('deliveryChargeField');
        const input = document.getElementById('hasDeliveryInput');
        const label = document.getElementById('hasDeliveryLabel');
        const isDelivery = checkbox.checked;

        field.style.display = isDelivery ? 'block' : 'none';
        input.value = isDelivery ? 'yes' : 'no';
        label.textContent = isDelivery ? 'Delivery required' : 'No delivery (self-collect)';
        recalculate();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Init Select2 on the first static row
        document.querySelectorAll('.select2-product').forEach(el => initSelect2(el));

        const deliveryInput = document.getElementById('deliveryChargeInput');
        if (deliveryInput) {
            deliveryInput.addEventListener('input', recalculate);
        }
        recalculate();
    });
</script>
@endpush
