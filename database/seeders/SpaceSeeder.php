<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = [
            [
                'name'           => 'Sala Andina',
                'slug'           => 'sala-andina',
                'type'           => 'conferencia',
                'capacity'       => 20,
                'description'    => 'Sala de conferencias premium con vista panorámica. Equipada con pantalla 4K, sistema de audio Dolby y pizarrón inteligente.',
                'price_per_hour' => 85000.00,
                'is_active'      => true,
            ],
            [
                'name'           => 'Sala Pacífico',
                'slug'           => 'sala-pacifico',
                'type'           => 'reunión',
                'capacity'       => 10,
                'description'    => 'Sala ejecutiva ideal para reuniones de equipo y entrevistas. Conexión de alta velocidad e iluminación ajustable.',
                'price_per_hour' => 55000.00,
                'is_active'      => true,
            ],
            [
                'name'           => 'Sala Caribe',
                'slug'           => 'sala-caribe',
                'type'           => 'webinar',
                'capacity'       => 50,
                'description'    => 'Auditorio virtual de alta capacidad para webinars, clases en línea y eventos corporativos masivos.',
                'price_per_hour' => 120000.00,
                'is_active'      => true,
            ],
        ];

        foreach ($spaces as $data) {
            $space = Space::firstOrCreate(['slug' => $data['slug']], $data);

            // Lunes a Viernes (1–5): 08:00–18:00
            foreach (range(1, 5) as $day) {
                Availability::firstOrCreate([
                    'space_id'    => $space->id,
                    'day_of_week' => $day,
                    'start_time'  => '08:00:00',
                    'end_time'    => '18:00:00',
                ]);
            }

            // Sábados (6): 09:00–13:00
            Availability::firstOrCreate([
                'space_id'    => $space->id,
                'day_of_week' => 6,
                'start_time'  => '09:00:00',
                'end_time'    => '13:00:00',
            ]);
        }

        $this->command->info('✅ 3 salas creadas: Sala Andina, Sala Pacífico, Sala Caribe.');
    }
}
