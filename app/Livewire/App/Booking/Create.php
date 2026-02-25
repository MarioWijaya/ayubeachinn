<?php

namespace App\Livewire\App\Booking;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Create extends Component
{
    public array $kamar = [];
    public array $tipeKamarOptions = [];
    public string $tipe_kamar = '';
    public string $kamar_id = '';

    public string $nama_tamu = '';
    public string $no_telp_tamu = '';
    public string $tanggal_range = '';
    public ?string $catatan = null;

    // LAYANAN
    public array $layananList = [];
    public ?string $layanan_id = null; // null / "1" / "2"
    public int $layanan_qty = 0;

    // MODAL
    public bool $confirmOpen = false;

    // SUMBER BOOKING + HARGA
    public string $source_type = '';
    public ?string $source_detail = null;
    public int $hargaKamar = 0; // harga per malam (dibayar tamu)
    public int $harga_kamar = 0; // alias untuk menjaga kompatibilitas snapshot lama

    public function mount(): void
    {
        $this->kamar = DB::table('kamar')
            ->select('id', 'nomor_kamar', 'tipe_kamar', 'status_kamar', 'tarif')
            ->orderBy('nomor_kamar')
            ->get()
            ->toArray();

        $this->tipeKamarOptions = DB::table('kamar')
            ->select('tipe_kamar')
            ->distinct()
            ->orderBy('tipe_kamar')
            ->pluck('tipe_kamar')
            ->filter(fn ($tipe) => filled($tipe))
            ->values()
            ->all();

        $this->layananList = DB::table('layanan')
            ->where('aktif', 1)
            ->orderBy('nama')
            ->get()
            ->toArray();

        $this->layanan_id = null;
        $this->layanan_qty = 0;

        $this->source_type = '';
        $this->source_detail = null;
        $this->setHargaKamar(0);
    }

    // =========================
    // UPDATED HOOKS
    // =========================

    public function updatedKamarId($value): void
    {
        // kalau walk-in → auto ambil tarif kamar
        $this->syncHargaKamarIfWalkIn();
    }

    public function updatedTipeKamar(): void
    {
        $this->kamar_id = '';
        $this->setHargaKamar(0);
    }

    public function updatedTanggalRange(string $value): void
    {
        $previous = $this->parseTanggalRange($value);
        if ($previous['in'] === null || $previous['out'] === null) {
            $this->tipe_kamar = '';
            $this->kamar_id = '';
            $this->setHargaKamar(0);
            return;
        }

        $this->tipe_kamar = '';
        $this->kamar_id = '';
        $this->setHargaKamar(0);
    }

    public function updatedSourceType(string $value): void
    {
        $this->source_type = $value;

        if (!in_array($this->source_type, ['ota', 'lainnya'], true)) {
            $this->source_detail = null;
        }

        $this->syncHargaKamarIfWalkIn();
    }

    public function updatedLayananId($value): void
    {
        if ($value === null || $value === '') {
            $this->layanan_id = null;
            $this->layanan_qty = 0;
            return;
        }

        $this->layanan_id = (string) $value;

        if ($this->layanan_qty <= 0) {
            $this->layanan_qty = 1;
        }
    }

    // =========================
    // WALK-IN HELPER
    // =========================

    public function applyWalkInTariff(): void
    {
        $this->syncHargaKamarIfWalkIn();
    }

    private function syncHargaKamarIfWalkIn(): void
    {
        if (!$this->isWalkIn()) {
            return;
        }

        $kamarId = (int) $this->kamar_id;
        if ($kamarId <= 0) {
            $this->setHargaKamar(0);
            return;
        }

        $tarif = DB::table('kamar')
            ->where('id', $kamarId)
            ->value('tarif');

        $this->setHargaKamar((int) ($tarif ?? 0));
    }

    public function isWalkIn(): bool
    {
        return $this->source_type === 'walk_in';
    }

    // =========================
    // LAYANAN ACTIONS
    // =========================

    public function toggleLayanan($layananId): void
    {
        if ($layananId === null) {
            $this->layanan_id = null;
            $this->layanan_qty = 0;
            return;
        }

        $id = (string) (int) $layananId;

        if ($this->layanan_id === $id) {
            $this->layanan_id = null;
            $this->layanan_qty = 0;
            return;
        }

        $this->layanan_id = $id;

        if ($this->layanan_qty <= 0) {
            $this->layanan_qty = 1;
        }
    }

    public function incQty(): void
    {
        if ($this->layanan_id === null) return;
        $this->layanan_qty = min(5, $this->layanan_qty + 1);
    }

    public function decQty(): void
    {
        if ($this->layanan_id === null) return;
        $this->layanan_qty = max(1, $this->layanan_qty - 1);
    }

    // =========================
    // VALIDATION
    // =========================

    public function rules(): array
    {
        return [
            'tipe_kamar'        => ['required', 'string', 'max:100'],
            'kamar_id'          => [
                'required',
                'exists:kamar,id',
                function (string $attribute, $value, \Closure $fail): void {
                    $kamarId = (int) $value;
                    $tipeKamar = mb_strtolower(trim($this->tipe_kamar));
                    $range = $this->parseTanggalRange($this->tanggal_range);

                    if ($kamarId <= 0 || $tipeKamar === '' || $range['in'] === null || $range['out'] === null) {
                        return;
                    }

                    $kamar = DB::table('kamar')
                        ->select('id', 'tipe_kamar', 'status_kamar')
                        ->where('id', $kamarId)
                        ->first();

                    if (! $kamar) {
                        return;
                    }

                    $kamarType = mb_strtolower(trim((string) $kamar->tipe_kamar));

                    if ($kamarType !== $tipeKamar) {
                        $fail('Nomor kamar tidak sesuai dengan tipe kamar yang dipilih.');
                    }

                    if (! $this->isKamarAvailableInRange($kamarId, $range['in'], $range['out'])) {
                        $fail('Kamar yang dipilih tidak tersedia pada tanggal tersebut.');
                    }
                },
            ],
            'nama_tamu'         => ['required', 'string', 'max:100'],
            'no_telp_tamu'      => ['nullable', 'string', 'max:20'],
            'hargaKamar'        => ['required', 'integer', 'min:0'],
            'tanggal_range'     => ['required', 'string'],
            'catatan'           => ['nullable', 'string'],
            'source_type'       => ['required', 'in:walk_in,telepon_wa,ota,lainnya'],
            'source_detail'     => ['nullable', 'string', 'max:100', 'required_if:source_type,ota,lainnya'],

            'layanan_id'        => ['nullable', 'exists:layanan,id'],
            'layanan_qty'       => ['required', 'integer', 'min:0', 'max:5'],
        ];
    }

    // =========================
    // MODAL OPEN/CLOSE
    // =========================

    public function openConfirm(): void
    {
        $this->validate();

        $parts = preg_split('/\s+to\s+/i', trim($this->tanggal_range));
        if (!$parts || count($parts) !== 2) {
            $this->addError('tanggal_range', 'Pilih rentang tanggal dari kalender.');
            return;
        }

        $checkIn  = trim($parts[0]);
        $checkOut = trim($parts[1]);

        if ($checkOut <= $checkIn) {
            $this->addError('tanggal_range', 'Check-out harus lebih besar dari check-in.');
            return;
        }

        if ($this->layanan_id !== null && (int)$this->layanan_qty <= 0) {
            $this->layanan_qty = 1;
        }

        $this->confirmOpen = true;
    }

    public function closeConfirm(): void
    {
        $this->confirmOpen = false;
    }

    // =========================
    // SAVE
    // =========================

    public function save()
    {
        $this->validate();

        $parts = preg_split('/\s+to\s+/i', trim($this->tanggal_range));
        if (!$parts || count($parts) !== 2) {
            $this->addError('tanggal_range', 'Pilih rentang tanggal dari kalender.');
            return;
        }

        $checkIn  = trim($parts[0]);
        $checkOut = trim($parts[1]);

        if ($checkOut <= $checkIn) {
            $this->addError('tanggal_range', 'Check-out harus lebih besar dari check-in.');
            return;
        }

        $kamarId = (int) $this->kamar_id;
        $range = $this->parseTanggalRange($this->tanggal_range);

        DB::transaction(function () use ($kamarId, $checkIn, $checkOut) {
            if (! $this->isKamarAvailableInRange($kamarId, $checkIn, $checkOut)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'kamar_id' => 'Kamar yang dipilih tidak tersedia pada tanggal tersebut.',
                ]);
            }

            $bookingId = DB::table('booking')->insertGetId([
                'kamar_id'          => $kamarId,
                'pegawai_id'        => Auth::id(),
                'nama_tamu'         => $this->nama_tamu,
                'no_telp_tamu'      => $this->no_telp_tamu !== '' ? $this->no_telp_tamu : null,
                'harga_kamar'       => (int) $this->hargaKamar,
                'source_type'       => $this->source_type,
                'source_detail'     => $this->normalizeSourceDetail($this->source_type, $this->source_detail),
                'tanggal_check_in'  => $checkIn,
                'tanggal_check_out' => $checkOut,
                'status_booking'    => 'menunggu',
                'status_updated_at' => now(),
                'catatan'           => $this->catatan ?: null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // simpan layanan kalau ada
            if ($this->layanan_id !== null && $this->layanan_qty > 0) {

                $layananId = (int) $this->layanan_id;

                $layanan = DB::table('layanan')
                    ->where('id', $layananId)
                    ->where('aktif', 1)
                    ->first();

                if ($layanan) {
                    $qty   = max(1, (int) $this->layanan_qty);
                    $harga = (int) $layanan->harga;

                    DB::table('booking_layanan')->insert([
                        'booking_id'   => $bookingId,
                        'layanan_id'   => $layananId,
                        'qty'          => $qty,
                        'harga_satuan' => $harga,
                        'subtotal'     => $qty * $harga,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        });

        session()->flash('success', 'Booking berhasil ditambahkan.');
        return redirect()->route($this->routePrefix() . '.booking.index');
    }

    private function routePrefix(): string
    {
        $user = Auth::user();
        $level = $user?->level;

        return match ($level) {
            'owner' => 'owner',
            'admin' => 'admin',
            default => 'pegawai',
        };
    }

    // =========================
    // COMPUTED PROPERTIES (MODAL)
    // =========================

    public function getSelectedKamarProperty(): ?object
    {
        $id = (int) $this->kamar_id;
        foreach ($this->kamar as $k) {
            $kId = (int) (is_array($k) ? ($k['id'] ?? 0) : ($k->id ?? 0));
            if ($kId === $id) {
                return is_array($k) ? (object) $k : $k;
            }
        }
        return null;
    }

    public function getTanggalPreviewProperty(): array
    {
        $parts = preg_split('/\s+to\s+/i', trim($this->tanggal_range));
        if (!$parts || count($parts) !== 2) return ['in' => null, 'out' => null];
        return ['in' => trim($parts[0]), 'out' => trim($parts[1])];
    }

    public function getSelectedLayananProperty()
    {
        $id = (int) $this->layanan_id;
        if ($id <= 0) return null;

        foreach ($this->layananList as $l) {
            if ((int) $l->id === $id) return $l;
        }
        return null;
    }

    public function getNightsProperty(): int
    {
        $tp = $this->tanggalPreview;
        if (empty($tp['in']) || empty($tp['out'])) return 0;

        try {
            $in  = Carbon::parse($tp['in'])->startOfDay();
            $out = Carbon::parse($tp['out'])->startOfDay();
            return max(0, (int) $in->diffInDays($out));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getLayananSubtotalProperty(): int
    {
        $layanan = $this->selectedLayanan;
        if (!$layanan || (int) $this->layanan_qty <= 0) return 0;

        return ((int) $layanan->harga) * ((int) $this->layanan_qty);
    }

    // ✅ INI FIX UTAMA: pakai harga_kamar inputan (bukan tarif kamar)
    public function getRoomSubtotalProperty(): int
    {
        return max(0, (int) $this->nights * (int) $this->hargaKamar);
    }

    public function updated(string $name, $value): void
    {
        if ($name === 'source_type') {
            if (!in_array($this->source_type, ['ota', 'lainnya'], true)) {
                $this->source_detail = null;
            }

            $this->syncHargaKamarIfWalkIn();
        }

        if ($name === 'kamar_id') {
            $this->syncHargaKamarIfWalkIn();
        }

        if ($name === 'hargaKamar') {
            $this->harga_kamar = (int) $value;
        }

        if ($name === 'harga_kamar') {
            $this->hargaKamar = (int) $value;
        }
    }

    private function setHargaKamar(int $value): void
    {
        $this->hargaKamar = $value;
        $this->harga_kamar = $value;
    }

    private function normalizeSourceDetail(string $type, ?string $detail): ?string
    {
        if (!in_array($type, ['ota', 'lainnya'], true)) {
            return null;
        }

        $clean = preg_replace('/\s+/', ' ', trim((string) $detail));
        return $clean !== '' ? $clean : null;
    }

    public function getGrandTotalProperty(): int
    {
        return (int) $this->roomSubtotal + (int) $this->layananSubtotal;
    }

    /**
     * @return array{in: string|null, out: string|null}
     */
    private function parseTanggalRange(string $value): array
    {
        $parts = preg_split('/\s+to\s+/i', trim($value));
        if (!$parts || count($parts) !== 2) {
            return ['in' => null, 'out' => null];
        }

        $checkIn = trim($parts[0]);
        $checkOut = trim($parts[1]);
        if ($checkIn === '' || $checkOut === '' || $checkOut <= $checkIn) {
            return ['in' => null, 'out' => null];
        }

        return ['in' => $checkIn, 'out' => $checkOut];
    }

    private function isKamarAvailableInRange(int $kamarId, string $checkIn, string $checkOut): bool
    {
        $hasBookingConflict = DB::table('booking')
            ->where('kamar_id', $kamarId)
            ->whereIn('status_booking', ['menunggu', 'check_in'])
            ->where('tanggal_check_in', '<', $checkOut)
            ->where('tanggal_check_out', '>', $checkIn)
            ->exists();

        if ($hasBookingConflict) {
            return false;
        }

        $hasMaintenanceConflict = DB::table('kamar_perbaikan')
            ->where('kamar_id', $kamarId)
            ->where('mulai', '<', $checkOut)
            ->where('selesai', '>=', $checkIn)
            ->exists();

        return ! $hasMaintenanceConflict;
    }

    // =========================
    // RENDER
    // =========================

    public function render()
    {
        return view('livewire.app.booking.create');
    }
}
