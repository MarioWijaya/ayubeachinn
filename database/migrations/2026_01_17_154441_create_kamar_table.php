<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_kamar')->unique();
            $table->string('tipe_kamar', 50);
            $table->decimal('tarif', 12, 2)->default(0);
            $table->unsignedInteger('kapasitas')->default(1);
            $table->enum('status_kamar', ['tersedia', 'terisi', 'perbaikan'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
