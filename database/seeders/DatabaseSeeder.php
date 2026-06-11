<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'nama' => 'Admin Utama',
            'username' => 'admin',
            'email' => 'admin@epicerie.com',
            'password' => bcrypt('admin123'),
            'role' => 'Pemilik',
            'pin_keamanan' => '123456',
        ]);
    }
}
