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
            if (!Schema::hasColumn('booking', 'checkout_at')) {
                $table->dateTime('checkout_at')->nullable()->after('status_booking');
            }

            if (!Schema::hasColumn('booking', 'denda')) {
                $table->unsignedBigInteger('denda')->default(0)->after('checkout_at');
            }

            if (!Schema::hasColumn('booking', 'total_kamar')) {
                $table->unsignedBigInteger('total_kamar')->nullable()->after('denda');
            }

            if (!Schema::hasColumn('booking', 'total_layanan')) {
                $table->unsignedBigInteger('total_layanan')->nullable()->after('total_kamar');
            }

            if (!Schema::hasColumn('booking', 'total_final')) {
                $table->unsignedBigInteger('total_final')->nullable()->after('total_layanan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'total_final')) {
                $table->dropColumn('total_final');
            }

            if (Schema::hasColumn('booking', 'total_layanan')) {
                $table->dropColumn('total_layanan');
            }

            if (Schema::hasColumn('booking', 'total_kamar')) {
                $table->dropColumn('total_kamar');
            }

            if (Schema::hasColumn('booking', 'denda')) {
                $table->dropColumn('denda');
            }

            if (Schema::hasColumn('booking', 'checkout_at')) {
                $table->dropColumn('checkout_at');
            }
        });
    }
};
