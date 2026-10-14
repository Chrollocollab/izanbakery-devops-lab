@extends('layouts.app')
@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="container" style="max-width:560px;">
        <h2 class="page-title text-center">Track Your Order</h2>
        <p class="text-center text-muted mb-4">Enter your order number and the email you used at checkout to see its status.</p>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('order.track.find') }}" method="POST" class="card">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="order_number" class="form-label">Order Number</label>
                    <input type="text" class="form-control @error('order_number') is-invalid @enderror"
                           id="order_number" name="order_number" value="{{ old('order_number', request('order_number')) }}"
                           placeholder="e.g. IZB-20260720-XXXXXXXX" required>
                    @error('order_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}"
                           placeholder="The email used at checkout" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Track Order</button>
            </div>
        </form>
    </section>
</main>
@endsection
