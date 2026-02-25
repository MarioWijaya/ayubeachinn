<?php

namespace App\Livewire\Admin\Pegawai;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Pegawai')]
class Edit extends Component
{
    public User $pegawai;

    public function mount(int $id): void
    {
        $this->pegawai = User::query()->findOrFail($id);
    }

    public function render(): View
    {
        return view('livewire.admin.pegawai.edit', [
            'pegawai' => $this->pegawai,
        ]);
    }
}
