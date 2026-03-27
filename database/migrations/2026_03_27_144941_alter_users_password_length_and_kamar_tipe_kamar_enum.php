<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $tipeKamarEnum = [
        'Standard Fan',
        'Superior',
        'Deluxe',
        'FamilyRoom',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 60)->change();
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('kamar', function (Blueprint $table) {
                $table->string('tipe_kamar', 50)->change();
            });

            return;
        }

        DB::table('kamar')
            ->where('tipe_kamar', 'Standart Fan')
            ->update(['tipe_kamar' => 'Standard Fan']);

        DB::table('kamar')
            ->where('tipe_kamar', 'Standard')
            ->update(['tipe_kamar' => 'Standard Fan']);

        DB::table('kamar')
            ->where('tipe_kamar', 'Family Room')
            ->update(['tipe_kamar' => 'FamilyRoom']);

        Schema::table('kamar', function (Blueprint $table) {
            $table->enum('tipe_kamar', $this->tipeKamarEnum)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 255)->change();
        });

        Schema::table('kamar', function (Blueprint $table) {
            $table->string('tipe_kamar', 50)->change();
        });
    }
};
