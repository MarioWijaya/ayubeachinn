<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KamarPerbaikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KamarController extends Controller
{
    public function index()
    {
        $kamar = DB::table('kamar')->orderBy('nomor_kamar')->get();
        return view('admin.kamar.index', compact('kamar'));
    }

    public function create()
    {
        return view('admin.kamar.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'nomor_kamar'  => ['required','string','max:10','unique:kamar,nomor_kamar'],
            'tipe_kamar'   => ['required','in:Standard Fan,Superior,Deluxe,Family Room'],
            'tarif'        => ['required','numeric','min:0'],
            'kapasitas'    => ['required','integer','min:1'],
            'status_kamar' => ['required','in:tersedia,perbaikan'],
            'perbaikan_mulai' => ['nullable', 'date', 'required_if:status_kamar,perbaikan'],
            'perbaikan_selesai' => ['nullable', 'date', 'required_if:status_kamar,perbaikan', 'after_or_equal:perbaikan_mulai'],
            'perbaikan_catatan' => ['nullable', 'string', 'max:500'],
        ];
        $request->validate($rules);

        $statusKamar = $request->status_kamar;
        $payload = [
            'nomor_kamar'  => $request->nomor_kamar,
            'tipe_kamar'   => $request->tipe_kamar,
            'tarif'        => $request->tarif,
            'kapasitas'    => $request->kapasitas,
            'status_kamar' => $statusKamar,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        $kamarId = DB::table('kamar')->insertGetId($payload);

        if ($statusKamar === 'perbaikan') {
            KamarPerbaikan::query()->updateOrCreate(
                ['kamar_id' => $kamarId],
                [
                    'mulai' => $request->perbaikan_mulai,
                    'selesai' => $request->perbaikan_selesai ?: null,
                    'catatan' => $request->perbaikan_catatan ?: null,
                ]
            );
        }

        return redirect()->route('admin.kamar.index')->with('success', 'Kamar berhasil ditambahkan.');
    }
    public function edit($id)
    {
        $kamar = DB::table('kamar')->where('id', $id)->first();
        abort_if(!$kamar, 404);

        $perbaikan = KamarPerbaikan::query()->where('kamar_id', $id)->first();
        $tipeKamar = ['Standard Fan', 'Superior', 'Deluxe', 'Family Room'];

        return view('admin.kamar.edit', compact('kamar', 'tipeKamar', 'perbaikan'));
    }

    public function update(Request $request, $id)
    {
        $kamar = DB::table('kamar')->where('id', $id)->first();
        abort_if(!$kamar, 404);

        $rules = [
            'nomor_kamar'  => ['required','string','max:10', 'unique:kamar,nomor_kamar,' . $id],
            'tipe_kamar'   => ['required','in:Standard Fan,Superior,Deluxe,Family Room'],
            'tarif'        => ['required','numeric','min:0'],
            'kapasitas'    => ['required','integer','min:1'],
            'status_kamar' => ['required','in:tersedia,perbaikan'],
            'perbaikan_mulai' => ['nullable', 'date', 'required_if:status_kamar,perbaikan'],
            'perbaikan_selesai' => ['nullable', 'date', 'required_if:status_kamar,perbaikan', 'after_or_equal:perbaikan_mulai'],
            'perbaikan_catatan' => ['nullable', 'string', 'max:500'],
        ];
        $request->validate($rules);

        $statusKamar = $request->status_kamar;
        $payload = [
            'nomor_kamar'  => $request->nomor_kamar,
            'tipe_kamar'   => $request->tipe_kamar,
            'tarif'        => $request->tarif,
            'kapasitas'    => $request->kapasitas,
            'status_kamar' => $statusKamar,
            'updated_at'   => now(),
        ];

        DB::table('kamar')->where('id', $id)->update($payload);

        if ($statusKamar === 'perbaikan') {
            KamarPerbaikan::query()->updateOrCreate(
                ['kamar_id' => $id],
                [
                    'mulai' => $request->perbaikan_mulai,
                    'selesai' => $request->perbaikan_selesai ?: null,
                    'catatan' => $request->perbaikan_catatan ?: null,
                ]
            );
        } else {
            KamarPerbaikan::query()->where('kamar_id', $id)->delete();
        }

        return redirect()->route('admin.kamar.index')->with('success', 'Kamar berhasil diupdate.');
}
}
