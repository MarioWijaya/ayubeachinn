<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kamar_id' => ['required', 'exists:kamar,id'],
            'nama_tamu' => ['required', 'string', 'max:100'],
            'no_telp_tamu' => ['nullable', 'string', 'max:20'],
            'sumber_booking_id' => ['nullable', 'exists:sumber_booking,id'],
            'harga_kamar' => ['required', 'integer', 'min:0'],
            'tanggal_check_in' => ['required', 'date'],
            'tanggal_check_out' => ['required', 'date', 'after:tanggal_check_in'],
            'status_booking' => ['required', 'in:menunggu,check_in,check_out,batal,selesai'],
            'catatan' => ['nullable', 'string'],
            'extra_bed' => ['required', 'boolean'],
            'extra_bed_tarif' => ['nullable', 'integer', 'min:0'],
            'extra_bed_qty' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
