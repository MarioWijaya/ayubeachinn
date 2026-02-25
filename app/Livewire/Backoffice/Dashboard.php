<?php

namespace App\Livewire\Backoffice;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = $this->from !== '' ? $this->from : '';
        $this->to = $this->to !== '' ? $this->to : '';
    }

    public function render(): View
    {
        return view('livewire.backoffice.dashboard');
    }
}
