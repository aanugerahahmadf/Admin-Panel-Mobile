<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terms_of_service', function (Blueprint $table) {
            $table->json('title_translations')->nullable()->after('title');
            $table->json('content_translations')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('terms_of_service', function (Blueprint $table) {
            $table->dropColumn(['title_translations', 'content_translations']);
        });
    }
};