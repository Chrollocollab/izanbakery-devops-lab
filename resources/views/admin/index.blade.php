@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">

            <!-- Bakery Welcome Banner -->
            <div class="bakery-welcome">
                <div>
                    <h4>Selamat datang, {{ Auth::user()->name ?? 'Admin' }}! &#127850;</h4>
                    <p>{{ now()->isoFormat('dddd, D MMMM YYYY') }} &mdash; Izanbakery Admin Panel</p>
                </div>
                <div class="bakery-icon">&#127856;</div>
            </div>

            <!-- Quick Stats -->
            <div class="tf-section-2 mb-30">
                <div class="flex gap20 flex-wrap-mobile">
                    <div class="w-half">
                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-shopping-bag"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Pesanan Hari Ini</div>
                                        <h4>{{ $todayStats['orders'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-half">
                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Pendapatan Hari Ini</div>
                                        <h4>RM {{ number_format($todayStats['revenue'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-half">
                        <div class="wg-chart-default">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap14">
                                    <div class="image ic-bg">
                                        <i class="icon-clock"></i>
                                    </div>
                                    <div>
                                        <div class="body-text mb-2">Pesanan Aktif</div>
                                        <h4>{{ $todayStats['pending'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders with Search -->
            <div class="tf-section mb-30">
                <div class="wg-box">
                    <div class="flex items-center justify-between mb-20">
                        <h5>Recent Orders</h5>
                        <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-primary">View All Orders</a>
                    </div>

                    <!-- Search Bar -->
                    <div class="search-box-wrapper">
                        <input type="text" id="orderSearch" class="search-input" placeholder="Search customer name or product ordered..." autocomplete="off">
                        <div id="searchResults" class="search-results"></div>
                    </div>

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
                                    @forelse($recentOrders as $order)
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
                                                <select class="status-select status-{{ $order->status }}" data-order-id="{{ $order->id }}" data-original="{{ $order->status }}">
                                                    @foreach($statuses as $key => $label)
                                                        <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
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
                                                @if($search)
                                                    No orders found matching "{{ $search }}"
                                                @else
                                                    No orders yet
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($recentOrders->hasPages())
                        <div class="divider"></div>
                        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                            {{ $recentOrders->links() }}
                        </div>
                    @endif
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
    .search-box-wrapper {
        position: relative;
        width: 100%;
        margin-bottom: 20px;
    }
    .search-input {
        width: 100%;
        height: 46px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    .search-input:focus {
        border-color: #2377FC;
        box-shadow: 0 0 0 3px rgba(35, 119, 252, 0.1);
    }
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-top: 4px;
        max-height: 350px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    .search-results.show {
        display: block;
    }
    .search-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.15s;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover,
    .search-result-item.active {
        background-color: #f8f9fa;
    }
    .search-result-item .result-info {
        flex: 1;
        min-width: 0;
    }
    .search-result-item .result-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    .search-result-item .result-subtext {
        font-size: 12px;
        color: #666;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .search-result-item .result-type {
        font-size: 10px;
        color: #fff;
        background: #2377FC;
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 500;
        text-transform: uppercase;
    }
    .search-no-results {
        padding: 16px;
        text-align: center;
        color: #888;
    }
    .status-select {
        padding: 6px 10px;
        border: 2px solid;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        min-width: 150px;
        transition: all 0.2s;
        color: #fff;
    }
    .status-select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }
    .status-select.updating {
        opacity: 0.6;
        pointer-events: none;
    }
    .status-select.success {
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
    }
    .status-select.error {
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.3);
    }
    /* Status colors */
    .status-select.status-pending_payment {
        background-color: #ffc107;
        border-color: #e0a800;
        color: #333;
    }
    .status-select.status-paid {
        background-color: #28a745;
        border-color: #218838;
    }
    .status-select.status-ready_for_pickup {
        background-color: #17a2b8;
        border-color: #138496;
    }
    .status-select.status-completed {
        background-color: #28a745;
        border-color: #218838;
    }
    .status-select.status-cancelled {
        background-color: #dc3545;
        border-color: #c82333;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Status change handler
        $('.status-select').on('change', function() {
            var $select = $(this);
            var orderId = $select.data('order-id');
            var newStatus = $select.val();
            var originalStatus = $select.data('original');
            
            $select.addClass('updating');
            
            $.ajax({
                url: '/admin/order/' + orderId + '/status',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function(response) {
                    // Update status class for color
                    $select.removeClass('status-' + originalStatus).addClass('status-' + newStatus);
                    $select.removeClass('updating').addClass('success');
                    $select.data('original', newStatus);
                    setTimeout(function() {
                        $select.removeClass('success');
                    }, 1500);
                },
                error: function(xhr) {
                    $select.removeClass('updating').addClass('error');
                    $select.val(originalStatus);
                    setTimeout(function() {
                        $select.removeClass('error');
                    }, 1500);
                    alert('Failed to update status. Please try again.');
                }
            });
        });

        var $input = $('#orderSearch');
        var $results = $('#searchResults');
        var searchTimeout;
        var activeIndex = -1;

        $input.on('input', function() {
            var term = $(this).val().trim();
            clearTimeout(searchTimeout);
            
            if (term.length < 1) {
                $results.removeClass('show').empty();
                return;
            }

            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route("admin.search") }}',
                    data: { term: term },
                    dataType: 'json',
                    success: function(data) {
                        renderResults(data.results);
                    }
                });
            }, 200);
        });

        function renderResults(results) {
            activeIndex = -1;
            if (results.length === 0) {
                $results.html('<div class="search-no-results">No results found</div>').addClass('show');
                return;
            }

            var html = '';
            results.forEach(function(item, index) {
                var subtextHtml = item.subtext ? '<div class="result-subtext">' + item.subtext + '</div>' : '';
                html += '<div class="search-result-item" data-url="' + item.url + '" data-index="' + index + '">' +
                    '<div class="result-info">' +
                        '<div class="result-name">' + item.text + '</div>' +
                        subtextHtml +
                    '</div>' +
                    '<span class="result-type">' + item.type + '</span>' +
                '</div>';
            });

            $results.html(html).addClass('show');
        }

        // Click on result
        $results.on('click', '.search-result-item', function() {
            window.location.href = $(this).data('url');
        });

        // Keyboard navigation
        $input.on('keydown', function(e) {
            var $items = $results.find('.search-result-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, $items.length - 1);
                updateActiveItem($items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                updateActiveItem($items);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                var url = $items.eq(activeIndex).data('url');
                if (url) window.location.href = url;
            } else if (e.key === 'Escape') {
                $results.removeClass('show');
            }
        });

        function updateActiveItem($items) {
            $items.removeClass('active');
            if (activeIndex >= 0) {
                $items.eq(activeIndex).addClass('active');
            }
        }

        // Close on click outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-box-wrapper').length) {
                $results.removeClass('show');
            }
        });

        // Show results on focus if there's content
        $input.on('focus', function() {
            if ($results.children().length > 0 && $(this).val().length >= 1) {
                $results.addClass('show');
            }
        });
    });
</script>
@endpush
