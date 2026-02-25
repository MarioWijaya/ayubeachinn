<?php

namespace App\Livewire\Backoffice\Kamar;

use App\Models\KamarPerbaikan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Kamar')]
class Edit extends Component
{
    public object $kamar;

    public function mount(int $id): void
    {
        $kamar = DB::table('kamar')->where('id', $id)->first();
        abort_if(!$kamar, 404);

        $this->kamar = $kamar;
    }

    public function render(): View
    {
        $tipeKamar = ['Standard Fan', 'Superior', 'Deluxe', 'Family Room'];
        $perbaikan = KamarPerbaikan::query()
            ->where('kamar_id', $this->kamar->id)
            ->first();

        return view('livewire.backoffice.kamar.edit', [
            'kamar' => $this->kamar,
            'tipeKamar' => $tipeKamar,
            'perbaikan' => $perbaikan,
        ]);
    }
}
