<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->timestamp('status_updated_at')->nullable()->after('status_booking');
            $table->string('canceled_reason', 50)->nullable()->after('status_updated_at');

            $table->index(['status_booking', 'status_updated_at'], 'booking_status_status_updated_at_index');
        });

        DB::table('booking')
            ->whereNull('status_updated_at')
            ->update(['status_updated_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropIndex('booking_status_status_updated_at_index');
            $table->dropColumn(['status_updated_at', 'canceled_reason']);
        });
    }
};
