<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('type')->default('package')->after('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['slug', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug', 'type']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
