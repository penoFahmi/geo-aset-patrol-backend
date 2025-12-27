<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name'              => 'Peno Fahmi',
            'email'             => 'penofahmi@gmail.com',
            'password'          => Hash::make('password123'),
            'role'              => 'admin',
            'nip'               => '999999999',
            'phone'             => '081234567890',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name'              => 'Petugas Lapangan',
            'email'             => 'adobecc241@gmail.com',
            'password'          => Hash::make('password123'),
            'role'              => 'officer',
            'nip'               => '123456789',
            'email_verified_at' => null,
        ]);
    }
}
