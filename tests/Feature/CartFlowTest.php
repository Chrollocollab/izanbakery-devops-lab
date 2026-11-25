<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Tests\TestCase;

class CartFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Remove any proof-of-payment files created during the test.
        Payment::all()->each(function (Payment $payment) {
            if ($payment->receipt_path) {
                File::delete(public_path('uploads/receipts/'.$payment->receipt_path));
            }
        });

        Cart::instance('cart')->destroy();

        parent::tearDown();
    }

    private function makeProduct(array $overrides = []): Product
    {
        static $i = 0;
        $i++;

        return Product::create(array_merge([
            'name' => 'Test Cake '.$i,
            'slug' => 'test-cake-'.$i,
            'description' => 'A delicious test cake.',
            'regular_price' => 20.00,
            'sale_price' => null,
            'SKU' => 'SKU-'.$i,
            'stock_status' => 'instock',
        ], $overrides));
    }

    /** Scenario 1: View empty cart → 200, empty state. */
    public function test_view_empty_cart(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Your order is empty', false);
        $this->assertSame(0, Cart::instance('cart')->content()->count());
    }

    /** Scenario 2: Add a product → item in cart with correct qty. */
    public function test_add_product_to_cart(): void
    {
        $product = $this->makeProduct();

        $response = $this->post(route('cart.add'), [
            'id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $content = Cart::instance('cart')->content();
        $this->assertSame(1, $content->count());
        $this->assertSame(2, $content->first()->qty);
        $this->assertEquals(20.00, $content->first()->price);
    }

    /** Scenario 3: Add the same product again → quantity merged, no duplicate row. */
    public function test_adding_same_product_again_increments_quantity(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 2]);
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 3]);

        $content = Cart::instance('cart')->content();
        $this->assertSame(1, $content->count(), 'Same product should not create a second row');
        $this->assertSame(5, $content->first()->qty);
    }

    /** Security: price is taken from the product, not the request. */
    public function test_add_to_cart_ignores_client_supplied_price(): void
    {
        $product = $this->makeProduct(['regular_price' => 50.00]);

        $this->post(route('cart.add'), [
            'id' => $product->id,
            'quantity' => 1,
            'price' => 1, // tampered
            'name' => 'Free Cake',
        ]);

        $row = Cart::instance('cart')->content()->first();
        $this->assertEquals(50.00, $row->price);
        $this->assertSame('Test Cake', substr($row->name, 0, 9));
    }

    /** Scenario 4: Add invalid product id → validation error, cart unchanged. */
    public function test_add_invalid_product_fails_validation(): void
    {
        $response = $this->from(route('shop.index'))
            ->post(route('cart.add'), ['id' => 9999, 'quantity' => 1]);

        $response->assertSessionHasErrors('id');
        $this->assertSame(0, Cart::instance('cart')->content()->count());
    }

    /** Scenario 5: Update quantity → reflected in cart. */
    public function test_update_cart_quantity(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 1]);
        $rowId = Cart::instance('cart')->content()->first()->rowId;

        $response = $this->put(route('cart.update', $rowId), ['quantity' => 5]);

        $response->assertRedirect();
        $this->assertSame(5, Cart::instance('cart')->get($rowId)->qty);
    }

    /** Scenario 6: Update quantity to 0 → validation error, qty unchanged. */
    public function test_update_cart_quantity_to_zero_fails(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 2]);
        $rowId = Cart::instance('cart')->content()->first()->rowId;

        $response = $this->from(route('cart.index'))
            ->put(route('cart.update', $rowId), ['quantity' => 0]);

        $response->assertSessionHasErrors('quantity');
        $this->assertSame(2, Cart::instance('cart')->get($rowId)->qty);
    }

    /** Updating a non-existent row → 404. */
    public function test_update_nonexistent_row_returns_404(): void
    {
        $this->put(route('cart.update', 'doesnotexist'), ['quantity' => 2])
            ->assertNotFound();
    }

    /** Scenario 10: Remove item → cart empty. */
    public function test_remove_cart_item(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 1]);
        $rowId = Cart::instance('cart')->content()->first()->rowId;

        $response = $this->delete(route('cart.remove', $rowId));

        $response->assertRedirect();
        $this->assertSame(0, Cart::instance('cart')->content()->count());
    }

    /** Scenario 11: Remove an already-removed/non-existent item → 404. */
    public function test_remove_nonexistent_item_returns_404(): void
    {
        $this->delete(route('cart.remove', 'doesnotexist'))->assertNotFound();
    }

    /** Scenario 7+9: Upload valid proof and place order → pending order + pending payment. */
    public function test_place_order_creates_pending_order_with_proof(): void
    {
        $product = $this->makeProduct(['regular_price' => 15.00]);
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 2]);

        $response = $this->post(route('cart.place-order'), [
            'full_name' => 'Wan Amirul',
            'phone' => '0123456789',
            'email' => 'wan@example.com',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '09:00',
            'proof_of_payment' => UploadedFile::fake()->image('receipt.png'),
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('cart.order.confirmation', $order->id));

        // Order is pending payment; totals computed server-side (15 * 2).
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertEquals(30.00, $order->total);
        $this->assertSame(1, $order->items()->count());

        // Payment is pending with a stored receipt.
        $this->assertNotNull($order->payment);
        $this->assertSame('pending', $order->payment->status);
        $this->assertNull($order->payment->paid_at);
        $this->assertNotNull($order->payment->receipt_path);
        $this->assertFileExists(public_path('uploads/receipts/'.$order->payment->receipt_path));

        // Cart is emptied after a successful order.
        $this->assertSame(0, Cart::instance('cart')->content()->count());
    }

    /** Scenario 8: Invalid file type → validation error, no order created. */
    public function test_place_order_rejects_invalid_file_type(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 1]);

        $response = $this->from(route('cart.checkout'))->post(route('cart.place-order'), [
            'full_name' => 'Wan Amirul',
            'phone' => '0123456789',
            'email' => 'wan@example.com',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '09:00',
            'proof_of_payment' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
        ]);

        $response->assertSessionHasErrors('proof_of_payment');
        $this->assertSame(0, Order::count());
    }

    /** Delivery without an address → validation error. */
    public function test_delivery_requires_address(): void
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 1]);

        $response = $this->from(route('cart.checkout'))->post(route('cart.place-order'), [
            'full_name' => 'Wan Amirul',
            'phone' => '0123456789',
            'email' => 'wan@example.com',
            'fulfillment_type' => 'delivery',
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '09:00',
            'proof_of_payment' => UploadedFile::fake()->image('receipt.png'),
        ]);

        $response->assertSessionHasErrors('address');
        $this->assertSame(0, Order::count());
    }

    /** Placing an order with an empty cart → redirected back to cart. */
    public function test_place_order_with_empty_cart_redirects(): void
    {
        $response = $this->post(route('cart.place-order'), [
            'full_name' => 'Wan Amirul',
            'phone' => '0123456789',
            'email' => 'wan@example.com',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '09:00',
            'proof_of_payment' => UploadedFile::fake()->image('receipt.png'),
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertSame(0, Order::count());
    }

    /** Admin marks payment done → payment completed, order paid, paid_at stamped. */
    public function test_admin_can_mark_payment_done(): void
    {
        $order = $this->placeOrderDirectly();
        $admin = User::factory()->create(['utype' => 'ADM']);

        $response = $this->actingAs($admin)
            ->put(route('admin.order.payment-done', $order->id));

        $response->assertRedirect();
        $order->refresh();
        $this->assertSame('completed', $order->payment->status);
        $this->assertNotNull($order->payment->paid_at);
        $this->assertSame(Order::STATUS_PAID, $order->status);
    }

    /** Non-admins cannot mark payment done. */
    public function test_non_admin_cannot_mark_payment_done(): void
    {
        $order = $this->placeOrderDirectly();
        $user = User::factory()->create(['utype' => 'USR']);

        $this->actingAs($user)
            ->put(route('admin.order.payment-done', $order->id))
            ->assertRedirect(route('login'));

        $this->assertSame('pending', $order->payment->fresh()->status);
    }

    /** Receipt is hidden in admin view until payment is marked done. */
    public function test_receipt_is_gated_until_payment_done(): void
    {
        $order = $this->placeOrderDirectly();
        $admin = User::factory()->create(['utype' => 'ADM']);
        $receiptUrl = $order->payment->receipt_url;

        // Before confirmation: receipt not shown.
        $before = $this->actingAs($admin)->get(route('admin.order.show', $order->id));
        $before->assertOk();
        $before->assertDontSee($receiptUrl, false);
        $before->assertSee('Mark Payment Done');

        // Confirm payment.
        $this->actingAs($admin)->put(route('admin.order.payment-done', $order->id));

        // After confirmation: receipt now visible.
        $after = $this->actingAs($admin)->get(route('admin.order.show', $order->id));
        $after->assertOk();
        $after->assertSee($order->payment->fresh()->receipt_url, false);
    }

    /**
     * Helper: place an order through the real HTTP flow and return it.
     */
    private function placeOrderDirectly(): Order
    {
        $product = $this->makeProduct();
        $this->post(route('cart.add'), ['id' => $product->id, 'quantity' => 1]);

        $this->post(route('cart.place-order'), [
            'full_name' => 'Wan Amirul',
            'phone' => '0123456789',
            'email' => 'wan@example.com',
            'fulfillment_type' => 'pickup',
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '09:00',
            'proof_of_payment' => UploadedFile::fake()->image('receipt.png'),
        ]);

        return Order::with('payment')->firstOrFail();
    }
}
