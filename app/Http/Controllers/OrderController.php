<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GeneratesReceiptPdf;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use GeneratesReceiptPdf;

    /**
     * Display a listing of orders with search and filter.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $ordersQuery = Order::with(['items', 'user'])
            ->orderBy('created_at', 'DESC');

        if ($search) {
            $ordersQuery->search($search);
        }

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->paginate(20);
        $statuses = Order::getStatuses();

        return view('admin.orders', compact('orders', 'search', 'status', 'statuses'));
    }

    /**
     * Show the form to manually create a new order.
     */
    public function create()
    {
        $products = Product::where('stock_status', 'instock')
            ->orderBy('name')
            ->get();
        $statuses = Order::getStatuses();

        return view('admin.order-create', compact('products', 'statuses'));
    }

    /**
     * Store a manually created order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'required|string|max:20',
            'address'           => 'required|string',
            'notes'             => 'nullable|string',
            'status'            => 'required|in:' . implode(',', array_keys(Order::getStatuses())),
            'has_delivery'      => 'required|in:yes,no',
            'delivery_charge'   => 'nullable|numeric|min:0',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        $deliveryCharge = $request->has_delivery === 'yes'
            ? (float) ($request->delivery_charge ?? 0)
            : 0.00;

        // Build order items and calculate subtotal
        $orderItems = [];
        $subtotal = 0;

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $price = $product->sale_price > 0 ? $product->sale_price : $product->regular_price;
            $qty = (int) $item['quantity'];
            $lineSubtotal = round($price * $qty, 2);
            $subtotal += $lineSubtotal;

            $orderItems[] = [
                'product'      => $product,
                'product_name' => $product->name,
                'price'        => $price,
                'quantity'     => $qty,
                'subtotal'     => $lineSubtotal,
            ];
        }

        $total = round($subtotal + $deliveryCharge, 2);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'status'       => $request->status,
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'notes'        => $request->notes,
            'subtotal'     => $subtotal,
            'shipping'     => $deliveryCharge,
            'discount'     => 0,
            'tax'          => 0,
            'total'        => $total,
        ]);

        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item['product']->id,
                'product_name' => $item['product_name'],
                'price'        => $item['price'],
                'quantity'     => $item['quantity'],
                'subtotal'     => $item['subtotal'],
            ]);
        }

        return redirect()
            ->route('admin.order.show', $order->id)
            ->with('success', 'Order ' . $order->order_number . ' created successfully.');
    }

    /**
     * Display a single order detail.
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'user', 'payment'])->findOrFail($id);
        $statuses = Order::getStatuses();

        return view('admin.order-details', compact('order', 'statuses'));
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'       => 'required|in:' . implode(',', array_keys(Order::getStatuses())),
            'status_label' => 'nullable|string|max:255',
        ]);

        $order = Order::with('payment')->findOrFail($id);
        $order->status = $request->status;
        $order->status_label = $request->status_label;

        if ($request->status === Order::STATUS_COMPLETED) {
            $order->delivered_at = now();
        } elseif ($request->status === Order::STATUS_CANCELLED) {
            $order->cancelled_at = now();
        }

        $order->save();

        // Keep the payment record in sync when an order is moved to "paid"
        // from here instead of via the dedicated "Mark Payment Done" button.
        // Manually-created orders may have no payment record at all — nothing to sync then.
        if ($request->status === Order::STATUS_PAID && $order->payment && $order->payment->status !== 'completed') {
            $order->payment->update([
                'status'  => 'completed',
                'paid_at' => now(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Order status updated successfully.',
                'status'      => $order->status,
                'status_name' => $order->status_name,
            ]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Mark an order's payment as done after the admin has verified the
     * bank transfer manually. Completes the payment, stamps paid_at, and
     * moves the order to `paid`. Only after this does the uploaded proof
     * of payment become visible in the admin order view.
     */
    public function markPaymentDone(Request $request, $id)
    {
        $order = Order::with('payment')->findOrFail($id);

        if (! $order->payment) {
            return back()->with('error', 'This order has no payment record to confirm.');
        }

        if ($order->payment->status === 'completed') {
            return back()->with('info', 'Payment was already marked as done.');
        }

        $order->payment->update([
            'status'  => 'completed',
            'paid_at' => now(),
        ]);

        $order->update(['status' => Order::STATUS_PAID]);

        return back()->with('success', 'Payment confirmed. Order marked as paid.');
    }

    /**
     * Generate and stream a PDF receipt for an order. Only available once
     * payment has been confirmed (paid, ready for pickup, or completed).
     */
    public function downloadReceipt($id)
    {
        $order = Order::with(['items', 'payment'])->findOrFail($id);

        if (! $order->isReceiptAvailable()) {
            return back()->with('error', 'A receipt is only available once payment has been confirmed.');
        }

        return $this->streamReceiptPdf($order);
    }

    /**
     * Delete an order (admin only, use with caution).
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()
            ->route('admin.orders')
            ->with('success', 'Order ' . $order->order_number . ' has been deleted.');
    }
}
