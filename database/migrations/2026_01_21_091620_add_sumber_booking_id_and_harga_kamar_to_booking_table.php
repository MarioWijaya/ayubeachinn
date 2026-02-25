<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            // sesuaikan posisi kolomnya jika mau pakai after()
            $table->foreignId('sumber_booking_id')
                ->nullable()
                ->constrained('sumber_booking')
                ->nullOnDelete();

            $table->unsignedInteger('harga_kamar')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_booking_id');
            $table->dropColumn('harga_kamar');
        });
    }
};