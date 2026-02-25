<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('perpanjangan_booking', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained('booking')->cascadeOnDelete();
            $table->date('tanggal_check_out_lama');
            $table->date('tanggal_check_out_baru');

            $table->foreignId('diperpanjang_oleh')->constrained('users')->restrictOnDelete();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perpanjangan_booking');
    }
};