<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('booking')->restrictOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedInteger('harga_satuan')->default(0);
            $table->unsignedInteger('subtotal')->default(0);
            $table->timestamps();

            $table->index(['booking_id', 'layanan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_layanan');
    }
};
