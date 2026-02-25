<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $seedTag = 'seed:booking-sample';
        $faker = fake();
        $startDate = Carbon::create(2024, 1, 1);
        $endDate = Carbon::create(2026, 12, 31);
        $today = now()->startOfDay();

        $pegawaiIds = DB::table('users')->where('level', 'pegawai')->pluck('id')->all();
        if ($pegawaiIds === []) {
            $pegawaiId = DB::table('users')->insertGetId([
                'nama' => 'Pegawai Seed',
                'username' => 'pegawai_seed_' . now()->format('YmdHis'),
                'password' => Hash::make('password'),
                'level' => 'pegawai',
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $pegawaiIds = [$pegawaiId];
        }

        $sumberBookingIds = [];
        $hasSumberBookingId = Schema::hasColumn('booking', 'sumber_booking_id');
        if ($hasSumberBookingId && Schema::hasTable('sumber_booking')) {
            $sumberBookingIds = DB::table('sumber_booking')->pluck('id')->all();

            if ($sumberBookingIds === []) {
                DB::table('sumber_booking')->insert([
                    [
                        'nama' => 'Walk-in',
                        'kode' => 'walk_in',
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama' => 'Telepon/WA',
                        'kode' => 'telepon_wa',
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama' => 'OTA',
                        'kode' => 'ota',
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama' => 'Lainnya',
                        'kode' => 'lainnya',
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                $sumberBookingIds = DB::table('sumber_booking')->pluck('id')->all();
            }
        }

        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('booking_layanan')) {
            DB::table('booking_layanan')->truncate();
        }
        if (Schema::hasTable('perpanjangan_booking')) {
            DB::table('perpanjangan_booking')->truncate();
        }
        if (Schema::hasTable('booking')) {
            DB::table('booking')->truncate();
        }
        Schema::enableForeignKeyConstraints();

        $rooms = DB::table('kamar')
            ->select('id', 'nomor_kamar', 'tarif')
            ->orderByRaw('CAST(nomor_kamar AS UNSIGNED) ASC')
            ->get();

        if ($rooms->isEmpty()) {
            return;
        }

        $sourceTypes = ['walk_in', 'telepon_wa', 'ota', 'lainnya'];
        $daysRange = $startDate->diffInDays($endDate) + 1;
        $hasSourceType = Schema::hasColumn('booking', 'source_type');
        $hasSourceDetail = Schema::hasColumn('booking', 'source_detail');
        $hasCheckoutAt = Schema::hasColumn('booking', 'checkout_at');
        $hasTotalFinal = Schema::hasColumn('booking', 'total_final');
        $hasTotalAkhir = Schema::hasColumn('booking', 'total_akhir');
        $hasTotal = Schema::hasColumn('booking', 'total');
        $batch = [];

        for ($dayOffset = 0; $dayOffset < $daysRange; $dayOffset++) {
            $checkInDate = $startDate->copy()->addDays($dayOffset);
            $checkOutDate = $checkInDate->copy()->addDay();
            $isPastStay = $checkOutDate->lessThanOrEqualTo($today);
            $isCheckoutToday = $checkOutDate->isSameDay($today);
            $isCheckInToday = $checkInDate->isSameDay($today);

            foreach ($rooms as $roomIndex => $room) {
                $pegawaiId = $pegawaiIds[array_rand($pegawaiIds)];
                $sumberBookingId = $sumberBookingIds !== [] ? $sumberBookingIds[array_rand($sumberBookingIds)] : null;

                $extraBed = $faker->boolean(25);
                $extraBedQty = $extraBed ? $faker->numberBetween(1, 2) : 1;
                $extraBedTarif = $extraBed ? $faker->randomElement([100000, 150000]) : null;

                $status = 'menunggu';
                if ($isPastStay) {
                    $status = (($dayOffset + $roomIndex) % 12 === 0) ? 'batal' : 'selesai';
                } elseif ($isCheckoutToday && (($dayOffset + $roomIndex) % 6 === 0)) {
                    $status = 'check_out';
                } elseif ($isCheckInToday) {
                    $status = (($dayOffset + $roomIndex) % 3 === 0) ? 'check_in' : 'menunggu';
                }

                $sourceType = $faker->randomElement($sourceTypes);
                $sourceDetail = in_array($sourceType, ['ota', 'lainnya'], true)
                    ? $faker->randomElement(['Traveloka', 'Agoda', 'Booking.com', 'Walk-in corporate', 'Offline agent'])
                    : null;

                $roomSubtotal = (int) $room->tarif;
                $extraBedSubtotal = $extraBed ? ((int) $extraBedTarif * $extraBedQty) : 0;
                $totalFinal = $roomSubtotal + $extraBedSubtotal;

                $payload = [
                    'kamar_id' => $room->id,
                    'pegawai_id' => $pegawaiId,
                    'nama_tamu' => $faker->name(),
                    'no_telp_tamu' => $faker->randomElement(['081234567890', '085712345678', '082198765432']),
                    'tanggal_check_in' => $checkInDate->toDateString(),
                    'tanggal_check_out' => $checkOutDate->toDateString(),
                    'status_booking' => $status,
                    'status_updated_at' => now(),
                    'catatan' => $seedTag,
                    'extra_bed' => $extraBed,
                    'extra_bed_tarif' => $extraBedTarif,
                    'extra_bed_qty' => $extraBedQty,
                    'harga_kamar' => (int) $room->tarif,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($hasSumberBookingId) {
                    $payload['sumber_booking_id'] = $sumberBookingId;
                }

                if ($hasSourceType) {
                    $payload['source_type'] = $sourceType;
                }

                if ($hasSourceDetail) {
                    $payload['source_detail'] = $sourceDetail;
                }

                if ($hasCheckoutAt) {
                    $payload['checkout_at'] = null;
                }

                if ($hasTotalFinal) {
                    $payload['total_final'] = null;
                } elseif ($hasTotalAkhir) {
                    $payload['total_akhir'] = null;
                } elseif ($hasTotal) {
                    $payload['total'] = null;
                }

                if (($status === 'selesai' || $status === 'check_out') && $hasCheckoutAt) {
                    $payload['checkout_at'] = $checkOutDate->copy()->setTime(11, 0)->toDateTimeString();
                }

                if ($status === 'selesai') {
                    if ($hasTotalFinal) {
                        $payload['total_final'] = $totalFinal;
                    } elseif ($hasTotalAkhir) {
                        $payload['total_akhir'] = $totalFinal;
                    } elseif ($hasTotal) {
                        $payload['total'] = $totalFinal;
                    }
                }

                $batch[] = $payload;

                if (count($batch) >= 1000) {
                    DB::table('booking')->insert($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            DB::table('booking')->insert($batch);
        }
    }
}
