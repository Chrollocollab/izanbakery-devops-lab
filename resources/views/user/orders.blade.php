@extends('layouts.app')
@section('content')
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">My Orders</h2>
        <div class="row">
            <div class="col-lg-3">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__dashboard">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order History</h5>
                        </div>
                        <div class="card-body">
                            @if($orders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th>Order Status</th>
                                                <th>Payment Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($orders as $order)
                                                <tr>
                                                    <td><strong>{{ $order->order_number }}</strong></td>
                                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                                    <td>{{ $order->items->count() }}</td>
                                                    <td>RM {{ number_format($order->total, 2) }}</td>
                                                    <td>
                                                        <span class="badge {{ $order->status_badge_class }}">
                                                            {{ $order->status_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($order->payment)
                                                            <span class="badge {{ $order->payment->status_badge }}">
                                                                {{ ucfirst($order->payment->status) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <a href="{{ route('user.orders.show', $order->id) }}" class="text-primary" title="View Details">
                                                                <i class="bi bi-eye fs-5"></i>
                                                            </a>
                                                            @if($order->isReceiptAvailable())
                                                                <a href="{{ route('user.orders.receipt', $order->id) }}" class="text-secondary" title="Download Receipt" target="_blank">
                                                                    <i class="bi bi-filetype-pdf fs-5"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center mt-3">
                                    {{ $orders->links() }}
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-bag fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">You haven't placed any orders yet.</p>
                                    <a href="{{ route('shop.index') }}" class="btn btn-primary mt-2">Start Shopping</a>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
