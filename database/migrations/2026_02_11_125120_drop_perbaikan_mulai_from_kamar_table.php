<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

   {
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            if (Schema::hasColumn('kamar', 'perbaikan_mulai')) {
                $table->dropColumn('perbaikan_mulai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            if (!Schema::hasColumn('kamar', 'perbaikan_mulai')) {
                $table->date('perbaikan_mulai')->nullable()->after('status_kamar');
            }
        });
    }
};
