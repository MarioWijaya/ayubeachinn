<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kamar_perbaikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')
                ->constrained('kamar')
                ->cascadeOnDelete();
            $table->date('mulai');
            $table->date('selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['kamar_id', 'mulai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kamar_perbaikan');
    }
};
