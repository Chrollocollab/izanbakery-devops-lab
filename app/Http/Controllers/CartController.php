<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    /**
     * Add a product to the cart.
     *
     * Price and name are resolved server-side from the product record so a
     * tampered form cannot dictate the price. Adding the same product again
     * stacks onto the existing row (Surfsidemedia derives the row id from the
     * id + name + options, so the quantity is merged automatically).
     */
    public function add_to_cart(Request $request)
    {
        $validated = $request->validate([
            'id'       => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['id']);
        $quantity = (int) ($validated['quantity'] ?? 1);
        $price = $product->sale_price > 0 ? $product->sale_price : $product->regular_price;

        Cart::instance('cart')
            ->add($product->id, $product->name, $quantity, $price)
            ->associate(Product::class);

        return redirect()->back()->with('success', 'Item added to your order.');
    }

    /**
     * Update the quantity of a single cart row.
     */
    public function update_cart(Request $request, $rowId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if (! Cart::instance('cart')->content()->has($rowId)) {
            abort(404);
        }

        Cart::instance('cart')->update($rowId, $validated['quantity']);

        return redirect()->back()->with('success', 'Cart updated.');
    }

    /**
     * Remove a single row from the cart.
     */
    public function remove_item($rowId)
    {
        if (! Cart::instance('cart')->content()->has($rowId)) {
            abort(404);
        }

        Cart::instance('cart')->remove($rowId);

        return redirect()->back()->with('success', 'Item removed from your order.');
    }

    public function checkout()
    {
        if (Auth::check() && ! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Please verify your email address before placing an order.');
        }

        $items = Cart::instance('cart')->content();

        if ($items->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        return view('user.checkout-details', compact('items'));
    }

    /**
     * Place an order from the current cart contents.
     *
     * The order is created with status `pending_payment` and an attached
     * payment record in `pending`. An admin later verifies the bank transfer
     * and marks the payment done. Prices are recomputed from the product
     * records, never trusted from the cart/session totals.
     */
    public function place_order(Request $request)
    {
        if (Auth::check() && ! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Please verify your email address before placing an order.');
        }

        $items = Cart::instance('cart')->content();

        if ($items->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $today = \Carbon\Carbon::today();
        $maxPickupDate = $today->copy()->addMonthNoOverflow()->endOfMonth();

        $timeSlots = [];
        for ($minutes = 8 * 60; $minutes <= 22 * 60; $minutes += 30) {
            $timeSlots[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        $validated = $request->validate([
            'full_name'         => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-\.]+$/u'],
            'phone'             => ['required', 'digits_between:10,11'],
            'email'             => 'required|email|max:255',
            'fulfillment_type'  => ['required', Rule::in(['pickup', 'delivery'])],
            'pickup_date'       => [
                'required',
                'date',
                'after_or_equal:' . $today->toDateString(),
                'before_or_equal:' . $maxPickupDate->toDateString(),
            ],
            'pickup_time'       => ['required', Rule::in($timeSlots)],
            'address'           => 'required_if:fulfillment_type,delivery|nullable|string|max:1000',
            'proof_of_payment'  => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        // Recompute totals from authoritative product prices.
        $subtotal = 0;
        $orderItems = [];

        foreach ($items as $row) {
            $product = Product::find($row->id);
            if (! $product) {
                continue;
            }
            $price = $product->sale_price > 0 ? $product->sale_price : $product->regular_price;
            $lineSubtotal = round($price * $row->qty, 2);
            $subtotal += $lineSubtotal;

            $orderItems[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'price'        => $price,
                'quantity'     => $row->qty,
                'subtotal'     => $lineSubtotal,
            ];
        }

        if (empty($orderItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'The products in your cart are no longer available.');
        }

        $total = round($subtotal, 2);

        $fulfillmentNote = ucfirst($validated['fulfillment_type'])
            . ' on ' . $validated['pickup_date'] . ' at ' . $validated['pickup_time'];

        $order = Order::create([
            'user_id'      => $request->user()?->id,
            'order_number' => Order::generateOrderNumber(),
            'status'       => Order::STATUS_PENDING_PAYMENT,
            'name'         => $validated['full_name'],
            'phone'        => $validated['phone'],
            'email'        => $validated['email'],
            'address'      => $validated['fulfillment_type'] === 'delivery'
                ? $validated['address']
                : 'Store pickup',
            'notes'        => $fulfillmentNote,
            'subtotal'     => $subtotal,
            'discount'     => 0,
            'tax'          => 0,
            'shipping'     => 0,
            'total'        => $total,
        ]);

        foreach ($orderItems as $item) {
            OrderItem::create(array_merge($item, ['order_id' => $order->id]));
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'method'   => 'qr_transfer',
            'amount'   => $total,
            'status'   => 'pending',
        ]);

        $payment->addMediaFromRequest('proof_of_payment')->toMediaCollection('receipt');

        $order->load('items');
        Mail::to($order->email)->queue(new OrderConfirmationMail($order));

        Notification::route('telegram', config('services.telegram-bot-api.admin_chat_id'))
            ->notify(new NewOrderPlaced($order));

        Notification::send(User::where('utype', 'ADM')->get(), new NewOrderPlaced($order));

        Cart::instance('cart')->destroy();

        // Guest checkouts have no user_id to check ownership against later,
        // so stamp this order's id into the session — it's the only proof
        // that this browser is the one that just placed it.
        if (! $order->user_id) {
            $request->session()->push('guest_order_ids', $order->id);
        }

        return redirect()->route('cart.order.confirmation', $order->id)
            ->with('success', 'Your order has been placed and is awaiting payment confirmation.');
    }

    /**
     * Thank-you / confirmation page after placing an order.
     */
    public function order_confirmation(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        $isOwner = $order->user_id
            ? $order->user_id === $request->user()?->id
            : in_array($order->id, $request->session()->get('guest_order_ids', []), true);

        abort_unless($isOwner, 403);

        return view('user.order-confirmation', compact('order'));
    }
}
