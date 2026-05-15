<?php

namespace Database\Seeders;

use App\Domain\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@videoconfreservas.com'],
            ['name' => 'Administrador', 'password' => bcrypt('password')]
        );

        $this->call([SpaceSeeder::class, DataSeeder::class]);
    }
}
