<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->json('title_translations')->nullable()->after('title');
            $table->json('subtitle_translations')->nullable()->after('subtitle');
            $table->json('faqs_translations')->nullable()->after('faqs');
        });
    }

    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropColumn(['title_translations', 'subtitle_translations', 'faqs_translations']);
        });
    }
};