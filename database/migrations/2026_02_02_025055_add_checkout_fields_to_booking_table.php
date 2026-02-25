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
    $table->dateTime('checkout_at')->nullable()->after('status_booking');
    $table->unsignedBigInteger('denda')->default(0)->after('checkout_at');
    $table->unsignedBigInteger('subtotal_kamar')->default(0)->after('denda');
    $table->unsignedBigInteger('subtotal_layanan')->default(0)->after('subtotal_kamar');
    $table->unsignedBigInteger('total_akhir')->default(0)->after('subtotal_layanan');
    $table->text('catatan_denda')->nullable()->after('total_akhir');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            //
        });
    }
};
