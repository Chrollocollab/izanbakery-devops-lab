<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old constraint first so the remap below isn't blocked by
        // it still only allowing the pre-existing method values.
        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_method_check");

        // fpx/card/ewallet were never actually offered at checkout — remap to
        // qr_transfer, the only online method the storefront supports.
        DB::table('payments')->whereIn('method', ['fpx', 'card', 'ewallet'])->update(['method' => 'qr_transfer']);

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_method_check CHECK (method IN ('cash', 'qr_transfer'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_method_check");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_method_check CHECK (method IN ('fpx', 'card', 'cash', 'ewallet'))");
    }
};
