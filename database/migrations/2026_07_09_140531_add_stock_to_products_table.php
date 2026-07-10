<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('base_price');
        });

        // Use Query Builder instead of raw SQL
        DB::table('products')
            ->where('stock', 0)
            ->update([
                'stock' => DB::raw('CASE WHEN in_stock = 1 THEN 10 ELSE 0 END')
            ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
