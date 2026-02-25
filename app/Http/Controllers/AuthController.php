<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function formLogin(): View
    {
        return view('pages.auth.login');
    }

    public function prosesLogin(Request $request): RedirectResponse
    {
    $request->validate([
        'username' => ['required'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($request->only('username', 'password'))) {
        return back()
            ->withErrors(['username' => 'Username atau password salah.'])
            ->onlyInput('username');
    }

        // login sukses
        $request->session()->regenerate();

    // ✅ cek status aktif dulu
    if (!Auth::user()->status_aktif) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withErrors(['login' => 'Akun kamu nonaktif.']);
    }

        $level = Auth::user()->level;

        return match ($level) {
            'owner'   => redirect()->route('owner.dashboard'),
            'admin'   => redirect()->route('admin.dashboard'),
            'pegawai' => redirect()->route('pegawai.dashboard'),
            default   => $this->logoutTidakValid($request),
        };
    }

    private function logoutTidakValid(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['username' => 'Level user tidak valid.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
