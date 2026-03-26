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
        $today = now()->toDateString();
        $this->from = $this->from !== '' ? $this->from : $today;
        $this->to = $this->to !== '' ? $this->to : $today;
    }

    public function render(): View
    {
        return view('livewire.backoffice.dashboard');
    }
}
