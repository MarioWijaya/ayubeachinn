<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perpanjangan_booking', function (Blueprint $table) {
            $table->enum('status_perpanjangan', ['aktif', 'dibatalkan'])->default('aktif')->after('tanggal_check_out_baru');
            $table->unsignedBigInteger('dibatalkan_oleh')->nullable()->after('diperpanjang_oleh');
            $table->timestamp('dibatalkan_pada')->nullable()->after('dibatalkan_oleh');
            $table->string('alasan_batal', 255)->nullable()->after('dibatalkan_pada');
        });
    }

    public function down(): void
    {
        Schema::table('perpanjangan_booking', function (Blueprint $table) {
            $table->dropColumn(['status_perpanjangan', 'dibatalkan_oleh', 'dibatalkan_pada', 'alasan_batal']);
        });
    }
};