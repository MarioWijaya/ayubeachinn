<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            if (Schema::hasColumn('kamar', 'perbaikan_selesai')) {
                $table->dropColumn('perbaikan_selesai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            if (!Schema::hasColumn('kamar', 'perbaikan_selesai')) {
                $table->date('perbaikan_selesai')->nullable()->after('perbaikan_mulai');
            }
        });
    }
};
