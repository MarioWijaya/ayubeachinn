<?php

namespace App\Livewire\Admin\Pegawai;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data Pegawai')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $pegawai = User::query()
            ->where('level', 'pegawai')
            ->when($this->q !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('nama', 'like', '%' . $this->q . '%')
                        ->orWhere('username', 'like', '%' . $this->q . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status_aktif', (bool) ((int) $this->status));
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('livewire.admin.pegawai.index', compact('pegawai'));
    }
}
