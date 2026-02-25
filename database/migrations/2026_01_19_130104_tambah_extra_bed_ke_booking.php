<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->boolean('extra_bed')->default(false)->after('catatan');
            $table->unsignedInteger('extra_bed_tarif')->nullable()->after('extra_bed'); // 100000 / 150000
            $table->unsignedInteger('extra_bed_qty')->default(1)->after('extra_bed_tarif');
        });
    }

    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn(['extra_bed', 'extra_bed_tarif', 'extra_bed_qty']);
        });
    }
};