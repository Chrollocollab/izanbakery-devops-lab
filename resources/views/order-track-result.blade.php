@extends('layouts.app')
@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="container" style="max-width:860px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h2 class="page-title mb-0">Order {{ $order->order_number }}</h2>
            <a href="{{ route('order.track') }}" class="btn btn-sm btn-outline-secondary">Track Another Order</a>
        </div>

        @include('user.order-status-cards', ['order' => $order])
    </section>
</main>
@endsection
