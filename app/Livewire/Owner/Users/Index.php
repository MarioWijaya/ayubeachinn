<?php

namespace App\Livewire\Owner\Users;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kelola User')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $level = '';
    public string $status = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'level' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedLevel(): void
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
        $this->level = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $users = User::query()
            ->when($this->q !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('nama', 'like', '%' . $this->q . '%')
                        ->orWhere('username', 'like', '%' . $this->q . '%');
                });
            })
            ->when($this->level !== '', function ($query) {
                $query->where('level', $this->level);
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status_aktif', (bool) ((int) $this->status));
            })
            ->orderBy('level')
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('livewire.owner.users.index', compact('users'));
    }
}
