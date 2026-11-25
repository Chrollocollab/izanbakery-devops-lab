<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to handle the CHECK constraint
        // First, drop the existing check constraint
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');

        // Change to varchar temporarily
        DB::statement('ALTER TABLE orders ALTER COLUMN status TYPE VARCHAR(50)');

        // Map old statuses to new ones
        DB::statement("UPDATE orders SET status = 'order_placed' WHERE status = 'new'");
        DB::statement("UPDATE orders SET status = 'pending_payment' WHERE status = 'pending'");

        // Add new check constraint with all new statuses
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('order_placed', 'processing', 'pending_payment', 'paid', 'ready_for_pickup', 'out_for_delivery', 'completed', 'cancelled'))");

        // Set default
        DB::statement("ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'order_placed'");

        // Add status_label column for custom notes/labels
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status_label')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status_label');
        });

        // Drop the new check constraint
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');

        // Map back to old statuses
        DB::statement("UPDATE orders SET status = 'new' WHERE status = 'order_placed'");
        DB::statement("UPDATE orders SET status = 'pending' WHERE status IN ('pending_payment', 'paid', 'ready_for_pickup', 'out_for_delivery')");

        // Add original check constraint
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('new', 'pending', 'processing', 'completed', 'cancelled'))");
        DB::statement("ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'new'");
    }
};
