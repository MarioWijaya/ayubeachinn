<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\KamarController;
use App\Http\Controllers\DashboardChartController;
use App\Http\Controllers\Pegawai\BookingController;
use App\Http\Controllers\Owner\UserController as OwnerUserController;

// OWNER (Livewire)
use App\Livewire\App\Booking\Index as AppBookingIndex;
use App\Livewire\App\Booking\Create as AppBookingCreate;
use App\Livewire\App\Booking\Edit as AppBookingEdit;
use App\Livewire\App\Booking\Show as AppBookingShow;
use App\Livewire\App\Calendar\Index as AppCalendarIndex;
use App\Livewire\Backoffice\Dashboard as BackofficeDashboard;
use App\Livewire\Backoffice\Kamar\Index as BackofficeKamarIndex;
use App\Livewire\Backoffice\Kamar\Create as BackofficeKamarCreate;
use App\Livewire\Backoffice\Kamar\Edit as BackofficeKamarEdit;
use App\Livewire\Backoffice\Reports\Revenue\Index as BackofficeRevenueIndex;
use App\Livewire\Pegawai\Dashboard as PegawaiDashboard;

use App\Livewire\Owner\Users\Index as OwnerUsersIndex;
use App\Livewire\Owner\Users\Create as OwnerUsersCreate;
use App\Livewire\Owner\Users\Edit as OwnerUsersEdit;

// ADMIN (Livewire)
use App\Livewire\Admin\Pegawai\Index as AdminPegawaiIndex;
use App\Livewire\Admin\Pegawai\Create as AdminPegawaiCreate;
use App\Livewire\Admin\Pegawai\Edit as AdminPegawaiEdit;

Route::get('/', fn () => redirect()->route('login'));

/**
 * AUTH
 */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'prosesLogin'])->name('login.proses');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/**
 * AUTHENTICATED
 */
Route::middleware('auth', 'active')->group(function () {

    // Redirect dashboard global
    Route::get('/dashboard', function () {
        $u = Auth::user();

        return match ($u->level) {
            'owner'   => redirect()->route('owner.dashboard'),
            'admin'   => redirect()->route('admin.dashboard'),
            'pegawai' => redirect()->route('pegawai.dashboard'),
            default   => redirect()->route('login'),
        };
    })->name('dashboard');

    Route::get('/dashboard/charts', [DashboardChartController::class, 'data'])
        ->name('dashboard.charts');

    /**
     * OWNER AREA
     */
    Route::middleware('level:owner')->prefix('owner')->as('owner.')->group(function () {

        Route::get('/dashboard', BackofficeDashboard::class)->name('dashboard');

        Route::get('/users', OwnerUsersIndex::class)->name('users.index');
        Route::get('/users/create', OwnerUsersCreate::class)->name('users.create');
        Route::post('/users', [OwnerUserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', OwnerUsersEdit::class)->whereNumber('id')->name('users.edit');
        Route::put('/users/{id}', [OwnerUserController::class, 'update'])->whereNumber('id')->name('users.update');
        Route::delete('/users/{id}', [OwnerUserController::class, 'destroy'])->whereNumber('id')->name('users.destroy');

        // KAMAR (Backoffice shared)
        Route::get('/kamar', BackofficeKamarIndex::class)->name('kamar.index');
        Route::get('/kamar/create', BackofficeKamarCreate::class)->name('kamar.create');
        Route::post('/kamar', [KamarController::class, 'store'])->name('kamar.store');
        Route::get('/kamar/{id}/edit', BackofficeKamarEdit::class)->whereNumber('id')->name('kamar.edit');
        Route::put('/kamar/{id}', [KamarController::class, 'update'])->whereNumber('id')->name('kamar.update');

        // BOOKING (shared)
        Route::get('/booking', AppBookingIndex::class)->name('booking.index');
        Route::get('/booking/create', AppBookingCreate::class)->name('booking.create');
        Route::get('/booking/{id}', AppBookingShow::class)->whereNumber('id')->name('booking.show');
        Route::get('/booking/{id}/edit', AppBookingEdit::class)->whereNumber('id')->name('booking.edit');

        // BOOKING ACTIONS (shared controller)
        Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
        Route::put('/booking/{id}', [BookingController::class, 'update'])->whereNumber('id')->name('booking.update');
        Route::get('/booking/kamar/{kamarId}/tanggal-terpakai', [BookingController::class, 'tanggalTerpakai'])
            ->whereNumber('kamarId')
            ->name('booking.tanggal_terpakai');
        Route::get('/booking/kamar-tersedia', [BookingController::class, 'kamarTersedia'])
            ->name('booking.kamar_tersedia');
        Route::get('/booking/kamar-tersedia-per-tipe', [BookingController::class, 'kamarTersediaPerTipe'])
            ->name('booking.kamar_tersedia_tipe');
        Route::get('/booking/kamar-availability', [BookingController::class, 'kamarAvailability'])
            ->name('booking.kamar_availability');
        Route::post('/booking/{id}/perpanjang', [BookingController::class, 'perpanjang'])
            ->whereNumber('id')
            ->name('booking.perpanjang');
        Route::post('/booking/{id}/checkout', [BookingController::class, 'checkout'])
            ->whereNumber('id')
            ->name('booking.checkout');
        Route::post('/booking/{id}/checkin', [BookingController::class, 'checkin'])
            ->whereNumber('id')
            ->name('booking.checkin');
        Route::post('/booking/{id}/selesai', [BookingController::class, 'selesai'])
            ->whereNumber('id')
            ->name('booking.selesai');
        Route::post('/booking/perpanjangan/{id}/batal', [BookingController::class, 'batalPerpanjangan'])
            ->whereNumber('id')
            ->name('perpanjangan.batal');

        // KALENDER (shared)
        Route::get('/calendar', AppCalendarIndex::class)->name('calendar.index');

        // REPORTS
        Route::get('/reports/revenue', BackofficeRevenueIndex::class)->name('reports.revenue.index');
    });

    /**
     * ADMIN AREA (owner + admin boleh masuk)
     */
    Route::middleware('level:owner,admin')->prefix('admin')->as('admin.')->group(function () {

        Route::get('/dashboard', BackofficeDashboard::class)->name('dashboard');

        // PEGAWAI
        Route::get('/pegawai', AdminPegawaiIndex::class)->name('pegawai.index');
        Route::get('/pegawai/create', AdminPegawaiCreate::class)->name('pegawai.create');
        Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/pegawai/{id}/edit', AdminPegawaiEdit::class)->whereNumber('id')->name('pegawai.edit');
        Route::put('/pegawai/{id}', [PegawaiController::class, 'update'])->whereNumber('id')->name('pegawai.update');
        Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->whereNumber('id')->name('pegawai.destroy');

        // KAMAR
        Route::get('/kamar', BackofficeKamarIndex::class)->name('kamar.index');
        Route::get('/kamar/create', BackofficeKamarCreate::class)->name('kamar.create');
        Route::post('/kamar', [KamarController::class, 'store'])->name('kamar.store');
        Route::get('/kamar/{id}/edit', BackofficeKamarEdit::class)->whereNumber('id')->name('kamar.edit');
        Route::put('/kamar/{id}', [KamarController::class, 'update'])->whereNumber('id')->name('kamar.update');

        // BOOKING (shared)
        Route::get('/booking', AppBookingIndex::class)->name('booking.index');
        Route::get('/booking/create', AppBookingCreate::class)->name('booking.create');
        Route::get('/booking/{id}', AppBookingShow::class)->whereNumber('id')->name('booking.show');
        Route::get('/booking/{id}/edit', AppBookingEdit::class)->whereNumber('id')->name('booking.edit');

        // BOOKING ACTIONS (shared controller)
        Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
        Route::put('/booking/{id}', [BookingController::class, 'update'])->whereNumber('id')->name('booking.update');
        Route::get('/booking/kamar/{kamarId}/tanggal-terpakai', [BookingController::class, 'tanggalTerpakai'])
            ->whereNumber('kamarId')
            ->name('booking.tanggal_terpakai');
        Route::get('/booking/kamar-tersedia', [BookingController::class, 'kamarTersedia'])
            ->name('booking.kamar_tersedia');
        Route::get('/booking/kamar-tersedia-per-tipe', [BookingController::class, 'kamarTersediaPerTipe'])
            ->name('booking.kamar_tersedia_tipe');
        Route::get('/booking/kamar-availability', [BookingController::class, 'kamarAvailability'])
            ->name('booking.kamar_availability');
        Route::post('/booking/{id}/perpanjang', [BookingController::class, 'perpanjang'])
            ->whereNumber('id')
            ->name('booking.perpanjang');
        Route::post('/booking/{id}/checkout', [BookingController::class, 'checkout'])
            ->whereNumber('id')
            ->name('booking.checkout');
        Route::post('/booking/{id}/checkin', [BookingController::class, 'checkin'])
            ->whereNumber('id')
            ->name('booking.checkin');
        Route::post('/booking/{id}/selesai', [BookingController::class, 'selesai'])
            ->whereNumber('id')
            ->name('booking.selesai');
        Route::post('/booking/perpanjangan/{id}/batal', [BookingController::class, 'batalPerpanjangan'])
            ->whereNumber('id')
            ->name('perpanjangan.batal');

        // KALENDER (shared)
        Route::get('/calendar', AppCalendarIndex::class)->name('calendar.index');

        // REPORTS
        Route::get('/reports/revenue', BackofficeRevenueIndex::class)->name('reports.revenue.index');
    });

    /**
     * PEGAWAI AREA
     */
    Route::middleware('level:pegawai')->prefix('pegawai')->as('pegawai.')->group(function () {

        Route::get('/dashboard', PegawaiDashboard::class)->name('dashboard');

        // Booking
        Route::get('/booking', AppBookingIndex::class)->name('booking.index');
        Route::get('/booking/create', AppBookingCreate::class)->name('booking.create');
        Route::get('/booking/{id}', AppBookingShow::class)->whereNumber('id')->name('booking.show');
        Route::get('/booking/{id}/edit', AppBookingEdit::class)->whereNumber('id')->name('booking.edit');

        // Actions (controller)
        Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
        Route::put('/booking/{id}', [BookingController::class, 'update'])->whereNumber('id')->name('booking.update');

        // AJAX tanggal terpakai
        Route::get('/booking/kamar/{kamarId}/tanggal-terpakai', [BookingController::class, 'tanggalTerpakai'])
            ->whereNumber('kamarId')
            ->name('booking.tanggal_terpakai');
        Route::get('/booking/kamar-tersedia', [BookingController::class, 'kamarTersedia'])
            ->name('booking.kamar_tersedia');
        Route::get('/booking/kamar-tersedia-per-tipe', [BookingController::class, 'kamarTersediaPerTipe'])
            ->name('booking.kamar_tersedia_tipe');
        Route::get('/booking/kamar-availability', [BookingController::class, 'kamarAvailability'])
            ->name('booking.kamar_availability');

        // Perpanjangan
        Route::post('/booking/{id}/perpanjang', [BookingController::class, 'perpanjang'])
            ->whereNumber('id')
            ->name('booking.perpanjang');
        Route::post('/booking/{id}/checkout', [BookingController::class, 'checkout'])
            ->whereNumber('id')
            ->name('booking.checkout');
        Route::post('/booking/{id}/checkin', [BookingController::class, 'checkin'])
            ->whereNumber('id')
            ->name('booking.checkin');
        Route::post('/booking/{id}/selesai', [BookingController::class, 'selesai'])
            ->whereNumber('id')
            ->name('booking.selesai');

        Route::post('/booking/perpanjangan/{id}/batal', [BookingController::class, 'batalPerpanjangan'])
            ->whereNumber('id')
            ->name('perpanjangan.batal');

        // ✅ Kalender (INI YANG BENAR)
        Route::get('/calendar', AppCalendarIndex::class)
            ->name('calendar.index');
    });
});

require __DIR__.'/settings.php';
