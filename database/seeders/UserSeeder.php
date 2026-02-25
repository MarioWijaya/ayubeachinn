<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'nama' => 'Owner',
                'level' => 'owner',
                'password' => Hash::make('owner123'),
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin',
                'level' => 'admin',
                'password' => Hash::make('admin'),
            ]
        );

        User::updateOrCreate(
            ['username' => 'aby'],
            [
                'nama' => 'Aby Pratama',
                'level' => 'pegawai',
                'password' => Hash::make('aby123'),
            ]
        );
    }
}
