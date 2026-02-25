<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class BookingController extends Controller
{
    private array $statusList = ['menunggu', 'check_in', 'batal'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        if ($from === '' && $to === '') {
            $from = now()->subDays(29)->toDateString();
            $to = now()->toDateString();
        }

        $tipeListSidebar = DB::table('kamar')
            ->select('tipe_kamar')
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        $query = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->join('users as pegawai', 'booking.pegawai_id', '=', 'pegawai.id')
            ->leftJoin('booking_layanan as bl', 'bl.booking_id', '=', 'booking.id')
            ->leftJoin('layanan as l', 'l.id', '=', 'bl.layanan_id')
            ->select(
                'booking.*',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                'kamar.tarif',
                'pegawai.nama as nama_pegawai',
                'pegawai.username as username_pegawai',
                'l.nama as layanan_nama',
                'bl.qty as layanan_qty',
                'bl.subtotal as layanan_subtotal',
                DB::raw('COALESCE(booking.harga_kamar, 0) + COALESCE(bl.subtotal, 0) as total_bayar')
            );

        if ($q !== '') {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('booking.id', 'like', '%' . $q . '%')
                    ->orWhere('booking.nama_tamu', 'like', '%' . $q . '%')
                    ->orWhere('kamar.nomor_kamar', 'like', '%' . $q . '%')
                    ->orWhere('kamar.tipe_kamar', 'like', '%' . $q . '%');
            });
        }

        if (is_string($status) && $status !== '') {
            $query->where('booking.status_booking', $status);
        }

        if ($from !== '') {
            $query->whereDate('booking.tanggal_check_in', '>=', $from);
        }

        if ($to !== '') {
            $query->whereDate('booking.tanggal_check_in', '<=', $to);
        }

        $booking = $query->orderByDesc('booking.id')->paginate(10)->withQueryString();

        return view('livewire.app.booking.index', compact(
            'tipeListSidebar',
            'booking',
            'q',
            'status',
            'from',
            'to'
        ));
    }

    public function show($id): View
    {
        $booking = DB::table('booking')
            ->join('kamar', 'booking.kamar_id', '=', 'kamar.id')
            ->join('users as pegawai', 'booking.pegawai_id', '=', 'pegawai.id')
            ->leftJoin('booking_layanan as bl', 'bl.booking_id', '=', 'booking.id')
            ->leftJoin('layanan as l', 'l.id', '=', 'bl.layanan_id')
            ->select(
                'booking.*',
                'kamar.nomor_kamar',
                'kamar.tipe_kamar',
                'kamar.tarif',
                'pegawai.nama as nama_pegawai',
                'pegawai.username as username_pegawai',
                'l.nama as layanan_nama',
                'bl.qty as layanan_qty',
                'bl.subtotal as layanan_subtotal'
            )
            ->where('booking.id', $id)
            ->first();

        abort_if(!$booking, 404);

        $riwayatPerpanjangan = DB::table('perpanjangan_booking')
            ->where('booking_id', $id)
            ->orderByDesc('id')
            ->get();

        $tipeListSidebar = DB::table('kamar')
            ->select('tipe_kamar')
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        return view('livewire.app.booking.show', compact(
            'booking',
            'riwayatPerpanjangan',
            'tipeListSidebar'
        ));
    }
    /**
     * OPTIONAL:
     * Kalau create booking sudah Livewire, method ini tidak dipakai.
     */
    public function create()
    {
        $kamar = DB::table('kamar')->orderBy('nomor_kamar')->get();

        $layananList = DB::table('layanan')
            ->where('aktif', 1)
            ->orderBy('nama')
            ->get();

        return view('livewire.app.booking.create', compact('kamar', 'layananList'));
    }

    /**
     * OPTIONAL:
     * Kalau create booking sudah Livewire, method ini juga tidak dipakai.
     * Tapi aku buat tetap kompatibel kalau masih dipanggil.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kamar_id' => ['required', 'exists:kamar,id'],
            'nama_tamu' => ['required', 'string', 'max:100'],
            'no_telp_tamu' => ['nullable', 'string', 'max:20'],
            'tanggal_check_in' => ['required', 'date'],
            'tanggal_check_out' => ['required', 'date', 'after:tanggal_check_in'],
            'catatan' => ['nullable', 'string'],
            'source_type' => ['required', 'in:walk_in,telepon_wa,ota,lainnya'],
            'source_detail' => ['nullable', 'string', 'max:100', 'required_if:source_type,ota,lainnya'],

            'layanan_id' => ['nullable', 'exists:layanan,id'],
            'layanan_qty' => ['nullable', 'integer', 'min:0', 'max:5'],
        ]);
        $validator->sometimes('layanan_qty', ['required', 'integer', 'min:1', 'max:5'], function ($input) {
            return !empty($input->layanan_id);
        });
        $validator->validate();

        $kamarId = (int) $request->kamar_id;
        $checkIn = $request->tanggal_check_in;
        $checkOut = $request->tanggal_check_out;

        // Anti double booking (aktif saja)
        $bentrok = DB::table('booking')
            ->where('kamar_id', $kamarId)
            ->whereIn('status_booking', ['menunggu', 'check_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('tanggal_check_in', '<', $checkOut)
                  ->where('tanggal_check_out', '>', $checkIn);
            })
            ->exists();

        if ($bentrok) {
            return back()
                ->withErrors(['tanggal_check_out' => 'Tanggal bentrok dengan booking lain (double booking).'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $kamarId, $checkIn, $checkOut) {
            $bookingId = DB::table('booking')->insertGetId([
                'kamar_id' => $kamarId,
                'pegawai_id' => Auth::id(),
                'nama_tamu' => $request->nama_tamu,
                'no_telp_tamu' => $request->no_telp_tamu ?: null,
                'tanggal_check_in' => $checkIn,
                'tanggal_check_out' => $checkOut,
                'status_booking' => 'menunggu',
                'status_updated_at' => now(),
                'source_type' => $request->source_type,
                'source_detail' => $this->normalizeSourceDetail($request->source_type, $request->source_detail),
                'catatan' => $request->catatan ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // layanan (opsional)
            $layananId = $request->layanan_id ? (int)$request->layanan_id : 0;
            if ($layananId > 0) {
                $qty = max(1, (int)($request->layanan_qty ?? 1));
                $layanan = DB::table('layanan')->where('id', $layananId)->where('aktif', 1)->first();
                if ($layanan) {
                    $harga = (int)$layanan->harga;

                    DB::table('booking_layanan')->insert([
                        'booking_id' => $bookingId,
                        'layanan_id' => $layananId,
                        'qty' => $qty,
                        'harga_satuan' => $harga,
                        'subtotal' => $harga * $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route($this->routePrefix().'.booking.index')->with('success', 'Booking berhasil ditambahkan.');
    }

    public function edit($id): View
    {
        $booking = Booking::query()->find($id);
        abort_if(!$booking, 404);

        if (in_array($booking->status_booking, ['check_out', 'selesai'], true)) {
            return redirect()
                ->route($this->routePrefix().'.booking.index')
                ->with('error', 'Booking tidak dapat diedit pada status ini.');
        }

        if ($booking->status_booking === 'batal') {
            return redirect()
                ->route($this->routePrefix().'.booking.index')
                ->with('error', 'Booking sudah dibatalkan dan tidak dapat diedit.');
        }

        try {
            Gate::authorize('update', $booking);
        } catch (AuthorizationException $exception) {
            return redirect()
                ->route($this->routePrefix().'.booking.index')
                ->with('error', $exception->getMessage());
        }

        $kamar = DB::table('kamar')->orderBy('nomor_kamar')->get();
        $statusList = $this->statusList;

        $layananList = DB::table('layanan')
            ->where('aktif', 1)
            ->orderBy('nama')
            ->get();

        $bl = DB::table('booking_layanan')->where('booking_id', $id)->first();

        $selectedLayananId = $bl ? (string)$bl->layanan_id : '';
        $selectedQty = $bl ? (int)$bl->qty : 0;
        $layananSubtotal = $bl ? (int)$bl->subtotal : 0;

        $checkIn = Carbon::parse($booking->tanggal_check_in)->startOfDay();
        $checkOut = Carbon::parse($booking->tanggal_check_out)->startOfDay();
        $currentNights = max(1, $checkIn->diffInDays($checkOut));
        $currentTotal = ($currentNights * (int) ($booking->harga_kamar ?? 0)) + $layananSubtotal;

        $tipeListSidebar = DB::table('kamar')
            ->select('tipe_kamar')
            ->whereNotNull('tipe_kamar')
            ->groupBy('tipe_kamar')
            ->orderBy('tipe_kamar')
            ->get();

        return view('livewire.app.booking.edit', compact(
            'booking',
            'kamar',
            'statusList',
            'layananList',
            'selectedLayananId',
            'selectedQty',
            'tipeListSidebar',
            'currentNights',
            'currentTotal'
        ));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::query()->find($id);
        abort_if(!$booking, 404);

        if (in_array($booking->status_booking, ['check_out', 'selesai'], true)) {
            return redirect()
                ->route($this->routePrefix().'.booking.index')
                ->with('error', 'Booking tidak dapat diedit pada status ini.');
        }

        if ($booking->status_booking === 'batal') {
            return redirect()
                ->route($this->routePrefix().'.booking.index')
                ->with('error', 'Booking sudah dibatalkan dan tidak dapat diedit.');
        }

        try {
            Gate::authorize('update', $booking);
        } catch (AuthorizationException $exception) {
            return redirect()
                ->route($this->routePrefix().'.booking.index')
                ->with('error', $exception->getMessage());
        }

        $validator = Validator::make($request->all(), [
            'kamar_id' => ['required', 'exists:kamar,id'],
            'nama_tamu' => ['required', 'string', 'max:100'],
            'no_telp_tamu' => ['nullable', 'string', 'max:20'],
            'tanggal_check_in' => ['required', 'date'],
            'tanggal_check_out' => ['required', 'date', 'after:tanggal_check_in'],
            'status_booking' => ['required', 'in:' . implode(',', $this->statusList)],
            'catatan' => ['nullable', 'string'],
            'source_type' => ['required', 'in:walk_in,telepon_wa,ota,lainnya'],
            'source_detail' => ['nullable', 'string', 'max:100', 'required_if:source_type,ota,lainnya'],

            // layanan (opsional)
            'layanan_id' => ['nullable', 'exists:layanan,id'],
            // kalau layanan_id kosong, qty boleh kosong
            'layanan_qty' => ['nullable', 'integer', 'min:0', 'max:5'],
        ]);
        $validator->sometimes('layanan_qty', ['required', 'integer', 'min:1', 'max:5'], function ($input) {
            return !empty($input->layanan_id);
        });
        $validator->validate();

        $oldKamarId = (int) $booking->kamar_id;
        $oldCheckOut = $booking->tanggal_check_out;
        $statusUpdatedAt = $booking->status_updated_at;

        if ($request->status_booking !== $booking->status_booking) {
            $statusUpdatedAt = now();
        }

        $kamarId = (int) $request->kamar_id;
        $checkIn = $request->tanggal_check_in;
        $checkOut = $request->tanggal_check_out;

        // anti double booking (exclude booking ini)
        $bentrok = DB::table('booking')
            ->where('kamar_id', $kamarId)
            ->where('id', '!=', $id)
            ->whereIn('status_booking', ['menunggu', 'check_in'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('tanggal_check_in', '<', $checkOut)
                  ->where('tanggal_check_out', '>', $checkIn);
            })
            ->exists();

        if ($bentrok) {
            return back()
                ->withErrors(['tanggal_check_out' => 'Tanggal bentrok dengan booking lain (double booking).'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $id, $kamarId, $oldKamarId, $oldCheckOut, $statusUpdatedAt) {

            // 1) update booking utama
            DB::table('booking')->where('id', $id)->update([
                'kamar_id' => $kamarId,
                'nama_tamu' => $request->nama_tamu,
                'no_telp_tamu' => $request->no_telp_tamu ?: null,
                'tanggal_check_in' => $request->tanggal_check_in,
                'tanggal_check_out' => $request->tanggal_check_out,
                'status_booking' => $request->status_booking,
                'status_updated_at' => $statusUpdatedAt,
                'source_type' => $request->source_type,
                'source_detail' => $this->normalizeSourceDetail($request->source_type, $request->source_detail),
                'catatan' => $request->catatan ?: null,
                'updated_at' => now(),
            ]);

            // 2) layanan (1 pilihan)
            $layananId = $request->layanan_id ? (int)$request->layanan_id : 0;

            if ($layananId <= 0) {
                // tidak ada layanan
                DB::table('booking_layanan')->where('booking_id', $id)->delete();
            } else {
                $qty = max(1, (int)($request->layanan_qty ?? 1));

                $layanan = DB::table('layanan')
                    ->where('id', $layananId)
                    ->where('aktif', 1)
                    ->first();

                if (!$layanan) {
                    DB::table('booking_layanan')->where('booking_id', $id)->delete();
                } else {
                    $harga = (int)$layanan->harga;
                    $subtotal = $harga * $qty;

                    $exists = DB::table('booking_layanan')->where('booking_id', $id)->exists();

                    if ($exists) {
                        DB::table('booking_layanan')->where('booking_id', $id)->update([
                            'layanan_id' => $layananId,
                            'qty' => $qty,
                            'harga_satuan' => $harga,
                            'subtotal' => $subtotal,
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('booking_layanan')->insert([
                            'booking_id' => $id,
                            'layanan_id' => $layananId,
                            'qty' => $qty,
                            'harga_satuan' => $harga,
                            'subtotal' => $subtotal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // 3) update status kamar (penting kalau kamar berubah)
            $newStatus = $request->status_booking;

            // helper: status membuat kamar TERISI?
            $isTerisi = ($newStatus === 'check_in');

            // helper: status membuat kamar TERSEDIA?
            $isTersedia = in_array($newStatus, ['check_out', 'selesai', 'batal'], true);

            // kalau kamar berubah, kamar lama harus diset tersedia (aman)
            if ($oldKamarId !== $kamarId) {
                DB::table('kamar')->where('id', $oldKamarId)->update([
                    'status_kamar' => 'tersedia',
                    'updated_at' => now(),
                ]);
            }

            if ($isTerisi) {
                DB::table('kamar')->where('id', $kamarId)->update([
                    'status_kamar' => 'terisi',
                    'updated_at' => now(),
                ]);
            }

            if ($isTersedia) {
                DB::table('kamar')->where('id', $kamarId)->update([
                    'status_kamar' => 'tersedia',
                    'updated_at' => now(),
                ]);
            }

            if ($request->tanggal_check_out > $oldCheckOut) {
                DB::table('perpanjangan_booking')->insert([
                    'booking_id' => $id,
                    'tanggal_check_out_lama' => $oldCheckOut,
                    'tanggal_check_out_baru' => $request->tanggal_check_out,
                    'diperpanjang_oleh' => Auth::id(),
                    'status_perpanjangan' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route($this->routePrefix().'.booking.index')->with('success', 'Booking berhasil diupdate.');
    }

    private function normalizeSourceDetail(string $type, ?string $detail): ?string
    {
        if (!in_array($type, ['ota', 'lainnya'], true)) {
            return null;
        }

        $clean = preg_replace('/\s+/', ' ', trim((string) $detail));
        return $clean !== '' ? $clean : null;
    }

    public function perpanjang(Request $request, $id)
    {
        $request->validate([
            'tanggal_check_out_baru' => ['required', 'date'],
        ]);

        $booking = DB::table('booking')->where('id', $id)->first();
        abort_if(!$booking, 404);

        $outLama = $booking->tanggal_check_out;
        $outBaru = $request->tanggal_check_out_baru;

        if ($outBaru <= $outLama) {
            return back()->withErrors([
                'tanggal_check_out_baru' => 'Tanggal check-out baru harus lebih besar dari tanggal sebelumnya.'
            ]);
        }

        // Cek bentrok booking lain setelah tanggal lama
        $bentrok = DB::table('booking')
            ->where('kamar_id', $booking->kamar_id)
            ->where('id', '!=', $booking->id)
            ->whereIn('status_booking', ['menunggu', 'check_in'])
            ->where(function ($q) use ($outLama, $outBaru) {
                $q->where('tanggal_check_in', '<', $outBaru)
                  ->where('tanggal_check_out', '>', $outLama);
            })
            ->exists();

        if ($bentrok) {
            return back()->withErrors([
                'tanggal_check_out_baru' => 'Tidak bisa perpanjang: ada booking lain setelahnya.'
            ]);
        }

        DB::transaction(function () use ($booking, $outLama, $outBaru) {
            DB::table('booking')->where('id', $booking->id)->update([
                'tanggal_check_out' => $outBaru,
                'updated_at' => now(),
            ]);

            DB::table('perpanjangan_booking')->insert([
                'booking_id' => $booking->id,
                'tanggal_check_out_lama' => $outLama,
                'tanggal_check_out_baru' => $outBaru,
                'diperpanjang_oleh' => Auth::id(),
                'status_perpanjangan' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route($this->routePrefix().'.booking.index')->with('success', 'Perpanjangan berhasil disimpan.');
    }

    private function routePrefix(): string
    {
        $role = Auth::user()?->level ?? 'pegawai';

        return in_array($role, ['owner', 'admin', 'pegawai'], true) ? $role : 'pegawai';
    }

    public function batalPerpanjangan(Request $request, $id)
    {
        $request->validate([
            'alasan_batal' => ['nullable', 'string', 'max:255'],
        ]);

        $perpanjangan = DB::table('perpanjangan_booking')->where('id', $id)->first();
        abort_if(!$perpanjangan, 404);

        if (($perpanjangan->status_perpanjangan ?? null) !== 'aktif') {
            return back()->withErrors(['alasan_batal' => 'Perpanjangan ini sudah tidak aktif / sudah dibatalkan.']);
        }

        DB::transaction(function () use ($perpanjangan, $request) {
            DB::table('booking')->where('id', $perpanjangan->booking_id)->update([
                'tanggal_check_out' => $perpanjangan->tanggal_check_out_lama,
                'updated_at' => now(),
            ]);

            DB::table('perpanjangan_booking')->where('id', $perpanjangan->id)->update([
                'status_perpanjangan' => 'dibatalkan',
                'dibatalkan_oleh' => Auth::id(),
                'dibatalkan_pada' => now(),
                'alasan_batal' => $request->alasan_batal,
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Perpanjangan berhasil dibatalkan.');
    }

    public function tanggalTerpakai(Request $request, $kamarId)
    {
        $excludeBookingId = (int) $request->query('exclude_booking_id', 0);

        $rows = DB::table('booking')
            ->where('kamar_id', (int)$kamarId)
            ->when($excludeBookingId > 0, function ($q) use ($excludeBookingId) {
                $q->where('id', '!=', $excludeBookingId);
            })
            ->whereIn('status_booking', ['menunggu', 'check_in']) // booking aktif saja
            ->select('tanggal_check_in', 'tanggal_check_out')
            ->get();

        $disabled = [];

        foreach ($rows as $r) {
            $start = new \DateTime($r->tanggal_check_in);
            $end   = new \DateTime($r->tanggal_check_out);

            $periodEnd = (clone $end)->modify('-1 day');
            if ($periodEnd < $start) continue;

            $period = new \DatePeriod(
                $start,
                new \DateInterval('P1D'),
                (clone $periodEnd)->modify('+1 day')
            );

            foreach ($period as $d) {
                $disabled[] = $d->format('Y-m-d');
            }
        }

        $disabled = array_values(array_unique($disabled));

        return response()->json(['disabled' => $disabled]);
    }

    public function kamarTersedia(Request $request)
    {
        $validated = $request->validate([
            'tanggal_check_in' => ['required', 'date'],
            'tanggal_check_out' => ['required', 'date', 'after:tanggal_check_in'],
            'exclude_booking_id' => ['nullable', 'integer'],
        ]);

        $excludeBookingId = (int) ($validated['exclude_booking_id'] ?? 0);

        $kamar = DB::table('kamar')
            ->whereNotExists(function ($query) use ($validated, $excludeBookingId) {
                $query->select(DB::raw(1))
                    ->from('booking')
                    ->whereColumn('booking.kamar_id', 'kamar.id')
                    ->when($excludeBookingId > 0, function ($subQuery) use ($excludeBookingId) {
                        $subQuery->where('booking.id', '!=', $excludeBookingId);
                    })
                    ->whereIn('booking.status_booking', ['menunggu', 'check_in'])
                    ->where('booking.tanggal_check_in', '<', $validated['tanggal_check_out'])
                    ->where('booking.tanggal_check_out', '>', $validated['tanggal_check_in']);
            })
            ->orderBy('nomor_kamar')
            ->get(['id', 'nomor_kamar', 'tipe_kamar']);

        return response()->json(['rooms' => $kamar]);
    }

    public function kamarTersediaPerTipe(Request $request)
    {
        $validated = $request->validate([
            'tipe_kamar' => ['required', 'string', 'max:100'],
        ]);

        $normalizedType = mb_strtolower(trim($validated['tipe_kamar']));
        $today = now()->toDateString();

        $rooms = DB::table('kamar')
            ->whereRaw('LOWER(TRIM(tipe_kamar)) = ?', [$normalizedType])
            ->whereNotExists(function ($query) use ($today) {
                $query->select(DB::raw(1))
                    ->from('booking')
                    ->whereColumn('booking.kamar_id', 'kamar.id')
                    ->whereIn('booking.status_booking', ['menunggu', 'check_in'])
                    ->whereDate('booking.tanggal_check_in', '<=', $today)
                    ->whereDate('booking.tanggal_check_out', '>', $today);
            })
            ->whereNotExists(function ($query) use ($today) {
                $query->select(DB::raw(1))
                    ->from('kamar_perbaikan')
                    ->whereColumn('kamar_perbaikan.kamar_id', 'kamar.id')
                    ->whereDate('kamar_perbaikan.mulai', '<=', $today)
                    ->whereDate('kamar_perbaikan.selesai', '>=', $today);
            })
            ->orderBy('nomor_kamar')
            ->get(['id', 'nomor_kamar', 'tipe_kamar']);

        $message = $rooms->isEmpty()
            ? 'Tidak ada kamar tersedia untuk tipe ini.'
            : null;

        return response()->json([
            'rooms' => $rooms,
            'message' => $message,
        ]);
    }

    public function kamarAvailability(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'tipe_kamar' => ['nullable', 'string', 'max:100'],
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $normalizedType = isset($validated['tipe_kamar'])
            ? mb_strtolower(trim($validated['tipe_kamar']))
            : null;

        $baseQuery = DB::table('kamar')
            ->whereNotExists(function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw(1))
                    ->from('booking')
                    ->whereColumn('booking.kamar_id', 'kamar.id')
                    ->whereIn('booking.status_booking', ['menunggu', 'check_in'])
                    ->where('booking.tanggal_check_in', '<', $endDate)
                    ->where('booking.tanggal_check_out', '>', $startDate);
            })
            ->whereNotExists(function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw(1))
                    ->from('kamar_perbaikan')
                    ->whereColumn('kamar_perbaikan.kamar_id', 'kamar.id')
                    ->where('kamar_perbaikan.mulai', '<', $endDate)
                    ->where('kamar_perbaikan.selesai', '>=', $startDate);
            });

        $types = (clone $baseQuery)
            ->select('tipe_kamar')
            ->distinct()
            ->orderBy('tipe_kamar')
            ->pluck('tipe_kamar')
            ->filter(fn ($tipe) => filled($tipe))
            ->values()
            ->all();

        $rooms = collect();
        if ($normalizedType !== null && $normalizedType !== '') {
            $rooms = (clone $baseQuery)
                ->whereRaw('LOWER(TRIM(tipe_kamar)) = ?', [$normalizedType])
                ->orderBy('nomor_kamar')
                ->get(['id', 'nomor_kamar', 'tipe_kamar']);
        }

        $message = null;
        if ($types === []) {
            $message = 'Tidak ada kamar tersedia pada tanggal tersebut.';
        } elseif ($normalizedType !== null && $normalizedType !== '' && $rooms->isEmpty()) {
            $message = 'Tidak ada kamar tersedia pada tanggal tersebut.';
        }

        return response()->json([
            'types' => $types,
            'rooms' => $rooms,
            'message' => $message,
        ]);
    }

    public function checkout(Request $request, int $id)
    {
        $request->validate([
            'denda' => ['nullable', 'integer', 'min:0'],
            'alasan_denda' => ['nullable', 'string', 'max:500'],
        ]);

        $booking = DB::table('booking')
            ->join('kamar', 'kamar.id', '=', 'booking.kamar_id')
            ->select('booking.*', 'kamar.tarif')
            ->where('booking.id', $id)
            ->first();

        abort_if(!$booking, 404);

        if (($booking->status_booking ?? null) !== 'check_in' || $booking->checkout_at !== null) {
            return back()->withErrors(['checkout' => 'Booking ini tidak bisa checkout.']);
        }

        $checkIn = Carbon::parse($booking->tanggal_check_in)->startOfDay();
        $checkOut = Carbon::parse($booking->tanggal_check_out)->startOfDay();
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $hargaPerMalam = (int) ($booking->harga_kamar ?? $booking->tarif ?? 0);
        $totalKamar = $nights * $hargaPerMalam;

        $totalLayanan = (int) DB::table('booking_layanan')
            ->where('booking_id', $id)
            ->sum('subtotal');

        $denda = max(0, (int) $request->input('denda', 0));
        $alasanDenda = trim((string) $request->input('alasan_denda', ''));
        $alasanDenda = $alasanDenda !== '' ? $alasanDenda : null;
        $totalFinal = $totalKamar + $totalLayanan + $denda;

        DB::transaction(function () use ($id, $totalKamar, $totalLayanan, $denda, $totalFinal, $alasanDenda) {
            DB::table('booking')->where('id', $id)->update([
                'status_booking' => 'check_out',
                'status_updated_at' => now(),
                'checkout_at' => now(),
                'denda' => $denda,
                'alasan_denda' => $alasanDenda,
                'total_kamar' => $totalKamar,
                'total_layanan' => $totalLayanan,
                'total_final' => $totalFinal,
                'updated_at' => now(),
            ]);
        });

        return redirect()->route($this->routePrefix().'.booking.index')->with('success', 'Checkout berhasil. Lanjutkan dengan konfirmasi Selesai.');
    }

    public function checkin(int $id)
    {
        $booking = DB::table('booking')
            ->select('id', 'kamar_id', 'status_booking')
            ->where('id', $id)
            ->first();

        abort_if(!$booking, 404);

        if (($booking->status_booking ?? null) !== 'menunggu') {
            return back()->withErrors(['checkin' => 'Booking ini tidak bisa check-in.']);
        }

        DB::transaction(function () use ($booking): void {
            DB::table('booking')->where('id', $booking->id)->update([
                'status_booking' => 'check_in',
                'status_updated_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kamar')->where('id', $booking->kamar_id)->update([
                'status_kamar' => 'terisi',
                'updated_at' => now(),
            ]);
        });

        return redirect()->route($this->routePrefix().'.booking.index')->with('success', 'Check-in berhasil. Silakan lanjutkan proses checkout saat tamu selesai menginap.');
    }

    public function selesai(int $id)
    {
        $booking = DB::table('booking')
            ->select('id', 'kamar_id', 'status_booking')
            ->where('id', $id)
            ->first();

        abort_if(!$booking, 404);

        if (($booking->status_booking ?? null) !== 'check_out') {
            return back()->withErrors(['selesai' => 'Booking ini belum siap dikonfirmasi selesai.']);
        }

        DB::transaction(function () use ($booking): void {
            DB::table('booking')->where('id', $booking->id)->update([
                'status_booking' => 'selesai',
                'status_updated_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('kamar')->where('id', $booking->kamar_id)->update([
                'status_kamar' => 'tersedia',
                'updated_at' => now(),
            ]);
        });

        return redirect()->route($this->routePrefix().'.booking.index')->with('success', 'Booking ditandai selesai. Kamar siap dipakai kembali.');
    }
    
}
