<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /**
     * Display the main statistics dashboard.
     * Optimized: Reduced from 50+ queries to ~10 queries
     */
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // === SINGLE QUERY: Get all order statistics at once ===
        $orderStats = Order::selectRaw("
            COUNT(*) as total_orders,
            COUNT(CASE WHEN status = 'pending_payment' THEN 1 END) as new_orders,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as pending_orders,
            COUNT(CASE WHEN status = 'ready_for_pickup' THEN 1 END) as processing_orders,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
            SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' AND DATE(created_at) = ? THEN total ELSE 0 END) as today_revenue,
            SUM(CASE WHEN status = 'completed' AND created_at >= ? THEN total ELSE 0 END) as weekly_revenue,
            SUM(CASE WHEN status = 'completed' AND created_at >= ? THEN total ELSE 0 END) as monthly_revenue,
            COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as orders_today,
            COUNT(CASE WHEN created_at >= ? THEN 1 END) as orders_weekly,
            COUNT(CASE WHEN created_at >= ? THEN 1 END) as orders_monthly
        ", [$today, $startOfWeek, $startOfMonth, $today, $startOfWeek, $startOfMonth])
            ->first();

        // === SINGLE QUERY: Revenue trend (last 30 days) ===
        $revenueTrendRaw = Order::selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->where('status', 'completed')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        // Fill missing dates with 0
        $revenueTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $revenueTrend[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'revenue' => (float) ($revenueTrendRaw[$date] ?? 0),
            ];
        }

        // === SINGLE QUERY: Order status distribution ===
        $orderStatusDistribution = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // === SINGLE QUERY: Top & least selling products ===
        $productSales = Product::select('products.id', 'products.name')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('products.id', 'products.name')
            ->selectRaw('SUM(order_items.quantity) as total_sold, SUM(order_items.subtotal) as total_revenue')
            ->orderByDesc('total_sold')
            ->get();

        $bestSelling = $productSales->take(5);
        $leastSelling = $productSales->sortBy('total_sold')->take(5);

        // === SINGLE QUERY: Category performance ===
        $categoryPerformance = Category::select('categories.id', 'categories.name')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', '=', 'completed');
            })
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            ->orderByDesc('total_revenue')
            ->get();

        // === SINGLE QUERY: Customer statistics ===
        $customerStats = User::selectRaw("
            COUNT(CASE WHEN utype = 'USR' THEN 1 END) as total_customers,
            COUNT(CASE WHEN utype = 'USR' AND created_at >= ? THEN 1 END) as new_customers
        ", [$startOfMonth])->first();

        // === SINGLE QUERY: Top customers ===
        $topCustomers = User::select('users.id', 'users.name')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', 'completed')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('SUM(orders.total) as total_spent, COUNT(orders.id) as order_count')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        // Returning customers count
        $returningCustomers = DB::table('orders')
            ->select('user_id')
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        // === SINGLE QUERY: Payment statistics ===
        $paymentStats = Payment::selectRaw("
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_total,
            COUNT(CASE WHEN status = 'refunded' THEN 1 END) as refund_count,
            SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) as refund_total,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
            SUM(CASE WHEN status = 'completed' AND DATE(paid_at) = ? THEN amount ELSE 0 END) as today_income
        ", [$today])->first();

        // === SINGLE QUERY: Payment methods breakdown ===
        $paymentMethods = Payment::selectRaw('method, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'completed')
            ->groupBy('method')
            ->get();

        // === SINGLE QUERY: Recent orders ===
        $recentOrders = Order::with(['user:id,name', 'payment:id,order_id,status'])
            ->select('id', 'order_number', 'user_id', 'name', 'total', 'status', 'created_at')
            ->latest()
            ->take(10)
            ->get();

        // === Build response arrays ===
        $completedCount = $orderStats->completed_orders ?: 1;

        $salesRevenue = [
            'today' => $orderStats->today_revenue ?? 0,
            'weekly' => $orderStats->weekly_revenue ?? 0,
            'monthly' => $orderStats->monthly_revenue ?? 0,
            'total' => $orderStats->total_revenue ?? 0,
            'orders_today' => $orderStats->orders_today ?? 0,
            'orders_weekly' => $orderStats->orders_weekly ?? 0,
            'orders_monthly' => $orderStats->orders_monthly ?? 0,
            'orders_total' => $orderStats->total_orders ?? 0,
            'aov' => $completedCount > 0 ? ($orderStats->total_revenue / $completedCount) : 0,
            'top_products' => $bestSelling,
        ];

        $ordersOverview = [
            'new' => $orderStats->new_orders ?? 0,
            'pending' => $orderStats->pending_orders ?? 0,
            'processing' => $orderStats->processing_orders ?? 0,
            'completed' => $orderStats->completed_orders ?? 0,
            'cancelled' => $orderStats->cancelled_orders ?? 0,
            'total' => $orderStats->total_orders ?? 0,
        ];

        $productPerformance = [
            'best_selling' => $bestSelling,
            'least_selling' => $leastSelling,
            'category_performance' => $categoryPerformance,
            'revenue_contribution' => $productSales->take(10),
            'total_revenue' => $orderStats->total_revenue ?? 0,
        ];

        $customersWithOrders = $topCustomers->count();
        $customerInsights = [
            'total' => $customerStats->total_customers ?? 0,
            'new' => $customerStats->new_customers ?? 0,
            'returning' => $returningCustomers,
            'top_customers' => $topCustomers,
            'purchase_frequency' => $customersWithOrders > 0
                ? round(($orderStats->total_orders ?? 0) / $customersWithOrders, 2)
                : 0,
        ];

        $paymentFinancial = [
            'payment_methods' => $paymentMethods,
            'daily_income' => [],
            'refunds' => $paymentStats->refund_total ?? 0,
            'refund_count' => $paymentStats->refund_count ?? 0,
            'failed_payments' => $paymentStats->failed_count ?? 0,
            'failed_amount' => 0,
            'today_income' => $paymentStats->today_income ?? 0,
        ];

        return view('admin.statistik', compact(
            'salesRevenue',
            'ordersOverview',
            'productPerformance',
            'customerInsights',
            'paymentFinancial',
            'revenueTrend',
            'orderStatusDistribution',
            'recentOrders'
        ));
    }

    /**
     * API endpoint for chart data.
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'revenue');

        switch ($type) {
            case 'revenue':
                $thirtyDaysAgo = Carbon::now()->subDays(30);
                $revenueTrendRaw = Order::selectRaw('DATE(created_at) as date, SUM(total) as revenue')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('date')
                    ->pluck('revenue', 'date')
                    ->toArray();

                $trend = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $trend[] = [
                        'date' => Carbon::parse($date)->format('M d'),
                        'revenue' => (float) ($revenueTrendRaw[$date] ?? 0),
                    ];
                }

                return response()->json($trend);

            case 'orders':
                return response()->json(
                    Order::selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray()
                );

            default:
                return response()->json(['error' => 'Invalid type']);
        }
    }
}
