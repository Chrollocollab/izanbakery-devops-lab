<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackController extends Controller
{
    /**
     * Show the public order-lookup form, for guests (and anyone else)
     * who don't have an account to see a "My Orders" list from.
     */
    public function show()
    {
        return view('order-track');
    }

    /**
     * Look up an order by order number + email. Both must match, so this
     * can't be used to browse other people's orders by guessing numbers.
     */
    public function find(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:50',
            'email'        => 'required|email|max:255',
        ]);

        $order = Order::with(['items', 'payment'])
            ->where('order_number', $validated['order_number'])
            ->where('email', $validated['email'])
            ->first();

        if (! $order) {
            return back()->withInput()->with('error', 'We could not find an order matching that order number and email.');
        }

        return view('order-track-result', compact('order'));
    }
}
