<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->json('features')->nullable()->after('is_featured');
            $table->string('theme')->nullable()->after('features');
            $table->string('color')->nullable()->after('theme');
            $table->integer('min_capacity')->nullable()->after('color');
            $table->integer('max_capacity')->nullable()->after('min_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'features', 'theme', 'color', 'min_capacity', 'max_capacity']);
        });
    }
};
