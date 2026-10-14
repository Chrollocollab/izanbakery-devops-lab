@extends('layouts.app')
@section('content')
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">Order {{ $order->order_number }}</h2>
        <div class="row">
            <div class="col-lg-3">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__dashboard">

                    <a href="{{ route('user.orders') }}" class="btn btn-sm btn-outline-secondary mb-3">
                        <i class="bi bi-arrow-left"></i> Back to My Orders
                    </a>

                    @include('user.order-status-cards', ['order' => $order, 'receiptUrl' => route('user.orders.receipt', $order->id)])

                </div>
            </div>
        </div>
    </section>
@endsection
