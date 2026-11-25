<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample customers if they don't exist
        $customers = $this->createCustomers();

        // Get all products with their categories
        $products = Product::with('category')->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        // Get all categories
        $categories = Category::all();

        // Payment methods
        $paymentMethods = ['cash', 'qr_transfer'];

        // Malaysian cities and states
        $locations = [
            ['city' => 'Kuala Lumpur', 'state' => 'Wilayah Persekutuan', 'postal' => '50000'],
            ['city' => 'Petaling Jaya', 'state' => 'Selangor', 'postal' => '46000'],
            ['city' => 'Shah Alam', 'state' => 'Selangor', 'postal' => '40000'],
            ['city' => 'Subang Jaya', 'state' => 'Selangor', 'postal' => '47500'],
            ['city' => 'Ampang', 'state' => 'Selangor', 'postal' => '68000'],
            ['city' => 'Klang', 'state' => 'Selangor', 'postal' => '41000'],
            ['city' => 'Putrajaya', 'state' => 'Wilayah Persekutuan', 'postal' => '62000'],
            ['city' => 'Cyberjaya', 'state' => 'Selangor', 'postal' => '63000'],
        ];

        // Assign popularity weights to products (some products sell more than others)
        $productWeights = [];
        foreach ($products as $product) {
            // Assign random popularity: 1 (low) to 5 (high)
            $productWeights[$product->id] = rand(1, 5);
        }

        $orderCount = 0;
        $totalDays = 90; // Generate orders for last 90 days for better trend analysis

        // PHASE 1: Guarantee each product appears in at least 3-5 completed orders
        $this->command->info('Phase 1: Seeding guaranteed orders for each product...');
        foreach ($products as $product) {
            $numGuaranteedOrders = rand(3, 5);

            for ($i = 0; $i < $numGuaranteedOrders; $i++) {
                $customer = $customers->random();
                $location = $locations[array_rand($locations)];
                $createdAt = Carbon::now()->subDays(rand(0, $totalDays))->subHours(rand(8, 20))->subMinutes(rand(0, 59));

                // 80% completed for statistics, 20% other statuses
                $status = rand(1, 100) <= 80 ? Order::STATUS_COMPLETED : $this->getRandomStatus();

                $quantity = rand(1, 4);
                $price = $product->sale_price ?? $product->regular_price;
                $subtotal = $price * $quantity;

                $order = $this->createOrder($customer, $location, $createdAt, $status, $subtotal);
                $this->createOrderItem($order, $product, $quantity, $price, $createdAt);
                $this->createPayment($order, $paymentMethods, $status, $createdAt);

                $orderCount++;
            }
        }

        // PHASE 2: Guarantee each category has diverse orders
        $this->command->info('Phase 2: Seeding category-diverse orders...');
        foreach ($categories as $category) {
            $categoryProducts = $products->where('category_id', $category->id);

            if ($categoryProducts->isEmpty()) {
                continue;
            }

            // Create 5-10 orders per category with mixed products from that category
            $numCategoryOrders = rand(5, 10);

            for ($i = 0; $i < $numCategoryOrders; $i++) {
                $customer = $customers->random();
                $location = $locations[array_rand($locations)];
                $createdAt = Carbon::now()->subDays(rand(0, $totalDays))->subHours(rand(8, 20))->subMinutes(rand(0, 59));
                $status = rand(1, 100) <= 75 ? Order::STATUS_COMPLETED : $this->getRandomStatus();

                // Pick 1-3 products from this category
                $numItems = min(rand(1, 3), $categoryProducts->count());
                $selectedProducts = $categoryProducts->random($numItems);

                $subtotal = 0;
                $orderItems = [];

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->sale_price ?? $product->regular_price;
                    $itemSubtotal = $price * $quantity;
                    $subtotal += $itemSubtotal;

                    $orderItems[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }

                $order = $this->createOrder($customer, $location, $createdAt, $status, $subtotal);

                foreach ($orderItems as $item) {
                    $this->createOrderItem($order, $item['product'], $item['quantity'], $item['price'], $createdAt);
                }

                $this->createPayment($order, $paymentMethods, $status, $createdAt);
                $orderCount++;
            }
        }

        // PHASE 3: Generate weighted random orders (popular products appear more)
        $this->command->info('Phase 3: Seeding weighted random orders...');
        $weightedProducts = [];
        foreach ($products as $product) {
            // Add product multiple times based on weight for weighted random selection
            for ($w = 0; $w < $productWeights[$product->id]; $w++) {
                $weightedProducts[] = $product;
            }
        }

        $numRandomOrders = 200;
        for ($i = 0; $i < $numRandomOrders; $i++) {
            $customer = $customers->random();
            $location = $locations[array_rand($locations)];

            // Distribute orders across time with some daily variance
            $daysAgo = rand(0, $totalDays);
            $hour = rand(8, 21); // Business hours bias
            $createdAt = Carbon::now()->subDays($daysAgo)->setHour($hour)->subMinutes(rand(0, 59));

            $status = $this->getWeightedStatus();

            // Pick 1-5 products using weighted selection
            $numItems = rand(1, 5);
            $selectedProductIds = [];
            $orderItems = [];
            $subtotal = 0;

            for ($j = 0; $j < $numItems; $j++) {
                $randomProduct = $weightedProducts[array_rand($weightedProducts)];

                // Avoid duplicate products in same order
                if (in_array($randomProduct->id, $selectedProductIds)) {
                    continue;
                }
                $selectedProductIds[] = $randomProduct->id;

                $quantity = rand(1, 4);
                $price = $randomProduct->sale_price ?? $randomProduct->regular_price;
                $itemSubtotal = $price * $quantity;
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'product' => $randomProduct,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            if (empty($orderItems)) {
                continue;
            }

            $order = $this->createOrder($customer, $location, $createdAt, $status, $subtotal);

            foreach ($orderItems as $item) {
                $this->createOrderItem($order, $item['product'], $item['quantity'], $item['price'], $createdAt);
            }

            $this->createPayment($order, $paymentMethods, $status, $createdAt);
            $orderCount++;
        }

        // PHASE 4: Create recent orders for "today" and "this week" statistics
        $this->command->info('Phase 4: Seeding recent orders for current period stats...');
        for ($i = 0; $i < 30; $i++) {
            $customer = $customers->random();
            $location = $locations[array_rand($locations)];

            // Last 7 days with emphasis on recent days
            $daysAgo = rand(0, 6);
            $hour = rand(9, 20);
            $createdAt = Carbon::now()->subDays($daysAgo)->setHour($hour)->subMinutes(rand(0, 59));

            $status = rand(1, 100) <= 70 ? Order::STATUS_COMPLETED : $this->getRandomStatus();

            $numItems = rand(1, 4);
            $selectedProducts = $products->random(min($numItems, $products->count()));

            $subtotal = 0;
            $orderItems = [];

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->sale_price ?? $product->regular_price;
                $itemSubtotal = $price * $quantity;
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $order = $this->createOrder($customer, $location, $createdAt, $status, $subtotal);

            foreach ($orderItems as $item) {
                $this->createOrderItem($order, $item['product'], $item['quantity'], $item['price'], $createdAt);
            }

            $this->createPayment($order, $paymentMethods, $status, $createdAt);
            $orderCount++;
        }

        $this->command->info("Orders seeded successfully! Created {$orderCount} orders.");
        $this->printStatisticsSummary($products, $categories);
    }

    /**
     * Get weighted random status (more completed orders).
     */
    private function getWeightedStatus(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 50) {
            return Order::STATUS_COMPLETED;
        }
        if ($rand <= 65) {
            return Order::STATUS_PAID;
        }
        if ($rand <= 80) {
            return Order::STATUS_READY_FOR_PICKUP;
        }
        if ($rand <= 94) {
            return Order::STATUS_PENDING_PAYMENT;
        }

        return Order::STATUS_CANCELLED;
    }

    /**
     * Get random status.
     */
    private function getRandomStatus(): string
    {
        $statuses = [
            Order::STATUS_PENDING_PAYMENT,
            Order::STATUS_PAID,
            Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ];

        return $statuses[array_rand($statuses)];
    }

    /**
     * Create an order.
     */
    private function createOrder($customer, $location, $createdAt, $status, $subtotal): Order
    {
        $tax = $subtotal * 0.06;
        $shipping = $subtotal > 100 ? 0 : 10.00;
        $discount = rand(0, 100) > 80 ? $subtotal * 0.1 : 0;
        $total = $subtotal + $tax + $shipping - $discount;

        return Order::create([
            'user_id' => $customer->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => $status,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'name' => $customer->name,
            'phone' => $customer->mobile ?? '01'.rand(10000000, 99999999),
            'email' => $customer->email,
            'address' => 'No. '.rand(1, 100).', Jalan '.rand(1, 20).'/'.rand(1, 15),
            'city' => $location['city'],
            'state' => $location['state'],
            'postal_code' => $location['postal'],
            'notes' => rand(0, 100) > 70 ? 'Tolong hantar sebelum pukul '.rand(10, 18).':00' : null,
            'delivered_at' => $status === Order::STATUS_COMPLETED ? $createdAt->copy()->addDays(rand(1, 3)) : null,
            'cancelled_at' => $status === Order::STATUS_CANCELLED ? $createdAt->copy()->addHours(rand(1, 24)) : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * Create an order item.
     */
    private function createOrderItem($order, $product, $quantity, $price, $createdAt): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * Create a payment.
     */
    private function createPayment($order, $paymentMethods, $status, $createdAt): Payment
    {
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
        // Determine payment status based on order status
        $completedStatuses = [
            Order::STATUS_COMPLETED,
            Order::STATUS_PAID,
            Order::STATUS_READY_FOR_PICKUP,
        ];

        if (in_array($status, $completedStatuses)) {
            $paymentStatus = 'completed';
        } elseif ($status === Order::STATUS_CANCELLED) {
            $paymentStatus = rand(0, 1) ? 'refunded' : 'failed';
        } else {
            $paymentStatus = rand(0, 100) > 20 ? 'completed' : 'pending';
        }

        return Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $paymentStatus === 'completed' ? strtoupper(uniqid('TXN')) : null,
            'method' => $paymentMethod,
            'amount' => $order->total,
            'status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'completed' ? $createdAt->copy()->addMinutes(rand(1, 30)) : null,
            'notes' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * Print statistics summary after seeding.
     */
    private function printStatisticsSummary($products, $categories): void
    {
        $this->command->newLine();
        $this->command->info('=== STATISTICS SUMMARY ===');

        // Order status breakdown
        $this->command->info('Order Status Breakdown:');
        foreach (Order::getStatuses() as $statusKey => $statusName) {
            $count = Order::where('status', $statusKey)->count();
            $this->command->line("  - {$statusName}: {$count}");
        }

        // Product sales summary (top 5)
        $this->command->newLine();
        $this->command->info('Top 5 Products by Quantity Sold:');
        $topProducts = Product::withSum(['orderItems as total_sold' => function ($query) {
            $query->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED));
        }], 'quantity')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        foreach ($topProducts as $idx => $product) {
            $rank = $idx + 1;
            $sold = $product->total_sold ?? 0;
            $this->command->line("  {$rank}. {$product->name}: {$sold} units");
        }

        // Category sales summary
        $this->command->newLine();
        $this->command->info('Sales by Category:');
        foreach ($categories as $category) {
            $productIds = $category->products->pluck('id');
            $totalSold = OrderItem::whereIn('product_id', $productIds)
                ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
                ->sum('quantity');
            $totalRevenue = OrderItem::whereIn('product_id', $productIds)
                ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
                ->sum('subtotal');
            $this->command->line("  - {$category->name}: {$totalSold} units, RM".number_format($totalRevenue, 2));
        }

        $this->command->newLine();
    }

    /**
     * Create sample customers.
     */
    private function createCustomers()
    {
        $customerNames = [
            ['name' => 'Ahmad bin Ibrahim', 'email' => 'ahmad.ibrahim@email.com'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.nur@email.com'],
            ['name' => 'Muhammad Ali', 'email' => 'muhammad.ali@email.com'],
            ['name' => 'Fatimah Zahra', 'email' => 'fatimah.z@email.com'],
            ['name' => 'Hassan Abdullah', 'email' => 'hassan.abd@email.com'],
            ['name' => 'Aisyah Rahman', 'email' => 'aisyah.r@email.com'],
            ['name' => 'Rizal Hakim', 'email' => 'rizal.hakim@email.com'],
            ['name' => 'Nurul Huda', 'email' => 'nurul.huda@email.com'],
            ['name' => 'Azman Kassim', 'email' => 'azman.k@email.com'],
            ['name' => 'Zainab Omar', 'email' => 'zainab.omar@email.com'],
            ['name' => 'Khairul Anwar', 'email' => 'khairul.a@email.com'],
            ['name' => 'Mariam Ismail', 'email' => 'mariam.i@email.com'],
            ['name' => 'Hafiz Razak', 'email' => 'hafiz.razak@email.com'],
            ['name' => 'Amira Yusof', 'email' => 'amira.y@email.com'],
            ['name' => 'Farid Hamid', 'email' => 'farid.hamid@email.com'],
            ['name' => 'Sarah Ahmad', 'email' => 'sarah.ahmad@email.com'],
            ['name' => 'Imran Malik', 'email' => 'imran.malik@email.com'],
            ['name' => 'Hana Sulaiman', 'email' => 'hana.s@email.com'],
            ['name' => 'Kamal Hassan', 'email' => 'kamal.h@email.com'],
            ['name' => 'Diana Rosli', 'email' => 'diana.rosli@email.com'],
        ];

        $customers = collect();

        foreach ($customerNames as $customer) {
            $user = User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'mobile' => '01'.rand(10000000, 99999999),
                    'password' => Hash::make('password123'),
                    'utype' => 'USR',
                    'created_at' => Carbon::now()->subDays(rand(30, 365)),
                ]
            );
            $customers->push($user);
        }

        return $customers;
    }
}
