<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status')->nullable()->after('identity_verified_at')
                ->comment('Status review KYC oleh admin: null=pending, verified, rejected');
            $table->decimal('face_similarity', 5, 2)->nullable()->after('kyc_status')
                ->comment('Skor kemiripan wajah (0-100) dari AI Core (FaceNet)');
            $table->decimal('face_deep_similarity', 5, 2)->nullable()->after('face_similarity')
                ->comment('Cosine similarity embedding FaceNet');
            $table->string('face_reason')->nullable()->after('face_deep_similarity')
                ->comment('Alasan hasil verifikasi wajah (MATCH/NO_MATCH/dll)');
            $table->json('face_liveness')->nullable()->after('face_reason')
                ->comment('Hasil pemeriksaan liveness/anti-spoof server');
            $table->boolean('liveness_completed')->default(false)->after('face_liveness')
                ->comment('Apakah liveness di perangkat selesai (instruksi gerakan)');
            $table->timestamp('face_verified_at')->nullable()->after('liveness_completed')
                ->comment('Kapan AI Core berhasil memverifikasi wajah');
            $table->foreignId('kyc_reviewed_by')->nullable()->after('face_verified_at')
                ->constrained('users')->nullOnDelete()
                ->comment('Admin yang melakukan review KYC');
            $table->timestamp('kyc_reviewed_at')->nullable()->after('kyc_reviewed_by')
                ->comment('Kapan admin melakukan review KYC');
            $table->text('kyc_notes')->nullable()->after('kyc_reviewed_at')
                ->comment('Catatan alasan approve/reject oleh admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kyc_reviewed_by');
            $table->dropColumn([
                'kyc_status',
                'face_similarity',
                'face_deep_similarity',
                'face_reason',
                'face_liveness',
                'liveness_completed',
                'face_verified_at',
                'kyc_reviewed_at',
                'kyc_notes',
            ]);
        });
    }
};
