<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->decimal('min_purchase', 15, 2)->default(0)->after('value');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_type', 'min_purchase']);
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->decimal('discount_amount', 15, 2)->nullable()->after('discount_id');
            $table->string('discount_type', 50)->nullable()->after('discount_amount');
            $table->decimal('min_purchase', 15, 2)->default(0)->after('discount_type');
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('min_purchase');
        });
    }
};
