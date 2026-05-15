<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\BlockedSlot;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class CleanDataSeeder extends Seeder
{
    public function run(): void
    {
        $originalSlugs = ['sala-andina', 'sala-pacifico', 'sala-caribe'];
        $originalEmails = ['admin@videoconfreservas.com'];

        Reservation::query()->delete();
        BlockedSlot::query()->delete();

        Space::whereNotIn('slug', $originalSlugs)->delete();

        User::whereNotIn('email', $originalEmails)->delete();

        $this->command->info('Datos de prueba eliminados. Solo quedan: admin, 3 salas base.');
    }
}
