<?php

namespace App\Livewire\Admin\Pegawai;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Pegawai')]
class Create extends Component
{
    public function render(): View
    {
        return view('livewire.admin.pegawai.create');
    }
}
