<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UserStoreRequest;
use App\Http\Requests\Owner\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(UserStoreRequest $request): RedirectResponse
    {
        User::query()->create([
            'nama' => $request->string('nama'),
            'username' => $request->string('username'),
            'password' => Hash::make((string) $request->string('password')),
            'level' => $request->string('level'),
            'status_aktif' => (bool) $request->boolean('status_aktif'),
        ]);

        return redirect()
            ->route('owner.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(UserUpdateRequest $request, int $id): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        $user->nama = $request->string('nama');
        $user->username = $request->string('username');
        $user->level = $request->string('level');
        $user->status_aktif = (bool) $request->boolean('status_aktif');

        if ($request->filled('password')) {
            $user->password = Hash::make((string) $request->string('password'));
        }

        $user->save();

        return redirect()
            ->route('owner.users.index')
            ->with('success', 'User berhasil diupdate.');
    }

    public function destroy(int $id): RedirectResponse
    {
        User::query()->whereKey($id)->delete();

        return redirect()
            ->route('owner.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
