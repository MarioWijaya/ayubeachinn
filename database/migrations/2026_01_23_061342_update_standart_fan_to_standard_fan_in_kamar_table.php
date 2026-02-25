<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('kamar')
            ->where('tipe_kamar', 'Standart Fan')
            ->update(['tipe_kamar' => 'Standard Fan']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('kamar')
            ->where('tipe_kamar', 'Standard Fan')
            ->update(['tipe_kamar' => 'Standart Fan']);
    }
};
