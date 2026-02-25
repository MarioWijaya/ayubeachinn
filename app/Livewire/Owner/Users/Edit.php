<?php

namespace App\Livewire\Owner\Users;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit User')]
class Edit extends Component
{
    public User $user;

    public function mount(int $id): void
    {
        $this->user = User::query()->findOrFail($id);
    }

    public function render(): View
    {
        return view('livewire.owner.users.edit', [
            'user' => $this->user,
        ]);
    }
}
