<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = (int) $this->route('id');

        return [
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $userId],
            'password' => ['nullable', 'string', 'min:6'],
            'level' => ['required', 'in:admin,pegawai,owner'],
            'status_aktif' => ['required', 'boolean'],
        ];
    }
}
