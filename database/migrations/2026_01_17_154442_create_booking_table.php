<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kamar_id')->constrained('kamar')->restrictOnDelete();
            $table->foreignId('pegawai_id')->constrained('users')->restrictOnDelete();

            $table->date('tanggal_check_in');
            $table->date('tanggal_check_out');

            $table->enum('status_booking', [
                'menunggu',
                'check_in',
                'check_out',
                'batal',
                'selesai'
            ])->default('menunggu');

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['kamar_id', 'tanggal_check_in', 'tanggal_check_out']);
            $table->index('status_booking');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};