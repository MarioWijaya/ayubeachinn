<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sumber_booking', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);      // Walk-in, Agoda, Traveloka, dll
            $table->string('kode', 50)->unique(); // walk_in, agoda, traveloka
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_booking');
    }
};
