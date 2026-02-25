<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingStoreRequest;
use App\Http\Requests\Admin\BookingUpdateRequest;
use App\Models\Booking;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // filter opsional
        $status = $request->get('status'); // menunggu/check_in/...
        $q = $request->get('q'); // cari nama tamu / nomor kamar

        $query = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->join('users as pegawai', 'booking.pegawai_id', '=', 'pegawai.id')
            ->select(
                'booking.*',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                'kamar.tarif',
                'pegawai.nama as nama_pegawai',
                'pegawai.username as username_pegawai'
            )
            ->whereIn('booking.status_booking', ['menunggu', 'check_in']); // sedang berlangsung

        if ($status) {
            $query->where('booking.status_booking', $status);
        }

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('booking.nama_tamu', 'like', "%$q%")
                  ->orWhere('kamar.nomor_kamar', 'like', "%$q%");
            });
        }

        $booking = $query->orderBy('booking.tanggal_check_in')->get();

        return view('admin.booking.index', compact('booking', 'status', 'q'));
    }

    public function show($id)
    {
        $booking = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->join('users as pegawai', 'booking.pegawai_id', '=', 'pegawai.id')
            ->select(
                'booking.*',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                'kamar.tarif',
                'pegawai.nama as nama_pegawai',
                'pegawai.username as username_pegawai'
            )
            ->where('booking.id', $id)
            ->first();

        abort_if(!$booking, 404);

        $riwayatPerpanjangan = DB::table('perpanjangan_booking')
            ->where('booking_id', $id)
            ->orderByDesc('id')
            ->get();

        return view('admin.booking.show', compact('booking', 'riwayatPerpanjangan'));
    }

    public function store(BookingStoreRequest $request): RedirectResponse
    {
        $extraBed = (bool) $request->boolean('extra_bed');

        DB::table('booking')->insert([
            'kamar_id' => (int) $request->input('kamar_id'),
            'pegawai_id' => (int) Auth::id(),
            'nama_tamu' => $request->string('nama_tamu'),
            'no_telp_tamu' => $request->input('no_telp_tamu'),
            'sumber_booking_id' => $request->input('sumber_booking_id'),
            'harga_kamar' => (int) $request->input('harga_kamar'),
            'tanggal_check_in' => $request->input('tanggal_check_in'),
            'tanggal_check_out' => $request->input('tanggal_check_out'),
            'status_booking' => $request->input('status_booking'),
            'status_updated_at' => now(),
            'catatan' => $request->input('catatan'),
            'extra_bed' => $extraBed,
            'extra_bed_tarif' => $extraBed ? $request->input('extra_bed_tarif') : null,
            'extra_bed_qty' => (int) $request->input('extra_bed_qty'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.booking.index')
            ->with('success', 'Booking berhasil ditambahkan.');
    }

    public function update(BookingUpdateRequest $request, int $id): RedirectResponse
    {
        $booking = Booking::query()->find($id);
        abort_if(!$booking, 404);

        if ($booking->status_booking === 'selesai') {
            return redirect()
                ->route('admin.booking.index')
                ->with('error', 'Booking sudah selesai dan tidak dapat diedit.');
        }

        if ($booking->status_booking === 'batal') {
            return redirect()
                ->route('admin.booking.index')
                ->with('error', 'Booking sudah dibatalkan dan tidak dapat diedit.');
        }

        try {
            Gate::authorize('update', $booking);
        } catch (AuthorizationException $exception) {
            return redirect()
                ->route('admin.booking.index')
                ->with('error', $exception->getMessage());
        }

        $extraBed = (bool) $request->boolean('extra_bed');
        $statusUpdatedAt = $booking->status_updated_at;

        if ($request->input('status_booking') !== $booking->status_booking) {
            $statusUpdatedAt = now();
        }

        DB::table('booking')->where('id', $id)->update([
            'kamar_id' => (int) $request->input('kamar_id'),
            'nama_tamu' => $request->string('nama_tamu'),
            'no_telp_tamu' => $request->input('no_telp_tamu'),
            'sumber_booking_id' => $request->input('sumber_booking_id'),
            'harga_kamar' => (int) $request->input('harga_kamar'),
            'tanggal_check_in' => $request->input('tanggal_check_in'),
            'tanggal_check_out' => $request->input('tanggal_check_out'),
            'status_booking' => $request->input('status_booking'),
            'status_updated_at' => $statusUpdatedAt,
            'catatan' => $request->input('catatan'),
            'extra_bed' => $extraBed,
            'extra_bed_tarif' => $extraBed ? $request->input('extra_bed_tarif') : null,
            'extra_bed_qty' => (int) $request->input('extra_bed_qty'),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.booking.index')
            ->with('success', 'Booking berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('booking')->where('id', $id)->delete();

        return redirect()
            ->route('admin.booking.index')
            ->with('success', 'Booking berhasil dihapus.');
    }

    public function batal(Request $request, $id)
    {
        $request->validate([
            'alasan_batal' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = DB::table('booking')->where('id', $id)->first();
        abort_if(!$booking, 404);

        // hanya booking aktif yg boleh dibatalkan
        if (!in_array($booking->status_booking, ['menunggu', 'check_in'], true)) {
            return back()->withErrors(['alasan_batal' => 'Booking ini tidak bisa dibatalkan (status sudah selesai).']);
        }

        DB::transaction(function () use ($booking, $request) {
            DB::table('booking')->where('id', $booking->id)->update([
                'status_booking' => 'batal',
                'status_updated_at' => now(),
                'catatan' => trim(($booking->catatan ?? '') . "\n[Admin Batal] " . ($request->alasan_batal ?? '-')),
                'updated_at' => now(),
            ]);

            // kamar kembali tersedia
            DB::table('kamar')->where('id', $booking->kamar_id)->update([
                'status_kamar' => 'tersedia',
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('admin.booking.index')->with('success', 'Booking berhasil dibatalkan.');
    }
    
}
