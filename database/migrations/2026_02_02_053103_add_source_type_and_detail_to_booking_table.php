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
        Schema::table('booking', function (Blueprint $table) {
            if (!Schema::hasColumn('booking', 'source_type')) {
                $table->string('source_type', 20)->default('walk_in')->after('harga_kamar');
            }

            if (!Schema::hasColumn('booking', 'source_detail')) {
                $table->string('source_detail', 100)->nullable()->after('source_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'source_detail')) {
                $table->dropColumn('source_detail');
            }

            if (Schema::hasColumn('booking', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};
