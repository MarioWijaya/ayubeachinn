<?php

namespace App\Livewire\Backoffice\Kamar;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Kamar')]
class Create extends Component
{
    public function render(): View
    {
        return view('livewire.backoffice.kamar.create');
    }
}
