<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GeneratesReceiptPdf;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MyOrderController extends Controller
{
    use GeneratesReceiptPdf;

    /**
     * List the authenticated user's own orders.
     */
    public function index()
    {
        $orders = Order::with(['items', 'payment'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.orders', compact('orders'));
    }

    /**
     * Show a single order belonging to the authenticated user.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'payment']);

        return view('user.order-detail', compact('order'));
    }

    /**
     * Download the PDF receipt for one of the authenticated user's own orders.
     */
    public function receipt(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $order->isReceiptAvailable()) {
            return back()->with('error', 'A receipt is only available once payment has been confirmed.');
        }

        $order->load('items');

        return $this->streamReceiptPdf($order);
    }
}
