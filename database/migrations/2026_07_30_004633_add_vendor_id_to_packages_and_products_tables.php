<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('packages', 'vendor_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete()->after('id');
            });
        }

        if (!Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('packages', 'vendor_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }

        if (Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }
    }
};
