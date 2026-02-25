<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('stores new room with selected status perbaikan from create form', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $response = $this->actingAs($user)->post(route('admin.kamar.store'), [
        'nomor_kamar' => '101',
        'tipe_kamar' => 'Superior',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'perbaikan',
        'perbaikan_mulai' => now()->toDateString(),
        'perbaikan_selesai' => now()->addDay()->toDateString(),
        'perbaikan_catatan' => 'Perbaikan AC',
    ]);

    $response->assertRedirect(route('admin.kamar.index'));
    $response->assertSessionHas('success', 'Kamar berhasil ditambahkan.');

    $kamarId = DB::table('kamar')->where('nomor_kamar', '101')->value('id');

    expect($kamarId)->not->toBeNull();
    expect(DB::table('kamar')->where('id', $kamarId)->value('status_kamar'))->toBe('perbaikan');
    expect(DB::table('kamar_perbaikan')->where('kamar_id', $kamarId)->exists())->toBeTrue();
});

it('requires perbaikan selesai when status kamar is perbaikan', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $response = $this->actingAs($user)->from(route('admin.kamar.create'))->post(route('admin.kamar.store'), [
        'nomor_kamar' => '102',
        'tipe_kamar' => 'Superior',
        'tarif' => 250000,
        'kapasitas' => 2,
        'status_kamar' => 'perbaikan',
        'perbaikan_mulai' => now()->toDateString(),
        'perbaikan_selesai' => '',
        'perbaikan_catatan' => 'Perbaikan AC',
    ]);

    $response->assertRedirect(route('admin.kamar.create'));
    $response->assertSessionHasErrors(['perbaikan_selesai']);
});

it('shows indonesian validation messages on kamar create form', function () {
    $user = User::factory()->create([
        'level' => 'admin',
        'status_aktif' => true,
    ]);

    $response = $this->actingAs($user)->from(route('admin.kamar.create'))->post(route('admin.kamar.store'), [
        'nomor_kamar' => '',
        'tipe_kamar' => '',
        'tarif' => 'abc',
        'kapasitas' => 0,
        'status_kamar' => 'tersedia',
    ]);

    $response->assertRedirect(route('admin.kamar.create'));
    $response->assertSessionHasErrors([
        'nomor_kamar' => 'Nomor kamar wajib diisi.',
        'tipe_kamar' => 'Tipe kamar wajib dipilih.',
        'tarif' => 'Tarif harus berupa angka.',
        'kapasitas' => 'Kapasitas minimal 1.',
    ]);
});
