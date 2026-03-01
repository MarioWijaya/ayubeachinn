<?php

use App\Livewire\App\Booking\Index as PegawaiBookingIndex;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function () {
    if (! Schema::hasTable('layanan')) {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('aktif')->default(true);
            $table->integer('harga')->default(0);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('booking_layanan')) {
        Schema::create('booking_layanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('layanan_id');
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedInteger('harga_satuan')->default(0);
            $table->unsignedInteger('subtotal')->default(0);
            $table->timestamps();
        });
    }
});

it('filters bookings by status and search query', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'A-101',
        'tipe_kamar' => 'Standard',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        [
            'kamar_id' => $kamarId,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Andi Filter',
            'tanggal_check_in' => now()->toDateString(),
            'tanggal_check_out' => now()->addDay()->toDateString(),
            'status_booking' => 'menunggu',
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => $kamarId,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Budi Lain',
            'tanggal_check_in' => now()->toDateString(),
            'tanggal_check_out' => now()->addDays(2)->toDateString(),
            'status_booking' => 'check_in',
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.index', [
            'q' => 'Andi',
            'status' => 'menunggu',
        ]))
        ->assertOk()
        ->assertSee('Andi Filter')
        ->assertDontSee('Budi Lain');
});

it('filters bookings by date range', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'A-102',
        'tipe_kamar' => 'Deluxe',
        'tarif' => 350000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        [
            'kamar_id' => $kamarId,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Rina Range',
            'tanggal_check_in' => '2026-01-01',
            'tanggal_check_out' => '2026-01-02',
            'status_booking' => 'menunggu',
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'kamar_id' => $kamarId,
            'pegawai_id' => $user->id,
            'nama_tamu' => 'Tono Jauh',
            'tanggal_check_in' => '2026-01-10',
            'tanggal_check_out' => '2026-01-11',
            'status_booking' => 'menunggu',
            'catatan' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.index', [
            'from' => '2026-01-01',
            'to' => '2026-01-05',
        ]))
        ->assertOk()
        ->assertSee('Rina Range')
        ->assertDontSee('Tono Jauh');
});

it('calculates total based on nights for booking list', function () {
    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $kamarId = DB::table('kamar')->insertGetId([
        'nomor_kamar' => 'A-103',
        'tipe_kamar' => 'Standard',
        'tarif' => 200000,
        'kapasitas' => 2,
        'status_kamar' => 'tersedia',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('booking')->insert([
        'kamar_id' => $kamarId,
        'pegawai_id' => $user->id,
        'nama_tamu' => 'Dina Total',
        'tanggal_check_in' => '2026-01-10',
        'tanggal_check_out' => '2026-01-12',
        'status_booking' => 'menunggu',
        'harga_kamar' => 123456,
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('pegawai.booking.index', [
            'from' => '2026-01-10',
            'to' => '2026-01-12',
        ]))
        ->assertOk()
        ->assertSee('Dina Total')
        ->assertSee('Rp 246.912')
        ->assertSee('Total Bayar');
});

it('sets date filters using quick range actions', function () {
    Carbon::setTestNow('2026-01-28');

    $user = User::factory()->create([
        'level' => 'pegawai',
    ]);

    $this->actingAs($user);

    Livewire::test(PegawaiBookingIndex::class)
        ->call('setQuickRange', 'today')
        ->assertSet('from', '2026-01-28')
        ->assertSet('to', '2026-01-28');

    Livewire::test(PegawaiBookingIndex::class)
        ->call('setQuickRange', 'week')
        ->assertSet('from', '2026-01-28')
        ->assertSet('to', '2026-02-03');

    Livewire::test(PegawaiBookingIndex::class)
        ->call('setQuickRange', 'month')
        ->assertSet('from', '2026-01-28')
        ->assertSet('to', '2026-02-26');

    Carbon::setTestNow();
});

it('prevents to date from being earlier than from date in booking filter', function () {
    Carbon::setTestNow('2026-02-04');

    Livewire::test(PegawaiBookingIndex::class)
        ->set('to', '2026-02-10')
        ->set('from', '2026-02-15')
        ->assertSet('to', '2026-02-15')
        ->assertSee('min="2026-02-15"', false);

    Carbon::setTestNow();
});

it('defaults booking filters to upcoming 30 days', function () {
    Carbon::setTestNow('2026-02-04');

    Livewire::test(PegawaiBookingIndex::class)
        ->assertSet('from', '2026-02-04')
        ->assertSet('to', '2026-03-05');

    Carbon::setTestNow();
});
