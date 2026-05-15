<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Availability;
use App\Domain\Models\BlockedSlot;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Generando datos de prueba...');

        $users = $this->createUsers();
        $spaces = $this->createSpaces();
        $this->createAvailabilities($spaces);
        $this->createBlockedSlots($spaces);
        $this->createReservations($spaces, $users);

        $this->command->info('Datos generados exitosamente.');
    }

    private function createUsers(): array
    {
        $names = [
            ['name' => 'Carlos Mendoza',   'email' => 'carlos@ejemplo.com'],
            ['name' => 'María García',     'email' => 'maria@ejemplo.com'],
            ['name' => 'Juan Pérez',       'email' => 'juan@ejemplo.com'],
            ['name' => 'Ana Rodríguez',    'email' => 'ana@ejemplo.com'],
            ['name' => 'Luis Martínez',    'email' => 'luis@ejemplo.com'],
            ['name' => 'Sofía López',      'email' => 'sofia@ejemplo.com'],
            ['name' => 'Diego Ramírez',    'email' => 'diego@ejemplo.com'],
            ['name' => 'Valentina Torres', 'email' => 'valentina@ejemplo.com'],
            ['name' => 'Andrés Castro',    'email' => 'andres@ejemplo.com'],
            ['name' => 'Camila Vargas',    'email' => 'camila@ejemplo.com'],
        ];

        $users = [];
        foreach ($names as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => bcrypt('password'),
                ]
            );
        }

        $this->command->info(count($users) . ' usuarios creados.');
        return $users;
    }

    private function createSpaces(): array
    {
        $newSpaces = [
            [
                'name'           => 'Sala Norte',
                'type'           => 'reunión',
                'capacity'       => 6,
                'description'    => 'Sala pequeña y acogedora para reuniones rápidas. Equipada con TV de 55" y sistema de videoconferencia.',
                'price_per_hour' => 35000.00,
            ],
            [
                'name'           => 'Sala Cumbre',
                'type'           => 'conferencia',
                'capacity'       => 30,
                'description'    => 'Gran salón de conferencias con capacidad para 30 personas. Incluye proyector 4K, micrófonos inalámbricos y catering.',
                'price_per_hour' => 150000.00,
            ],
            [
                'name'           => 'Sala Horizonte',
                'type'           => 'webinar',
                'capacity'       => 100,
                'description'    => 'Auditorio digital con transmisión en vivo, pantalla LED gigante y sonido envolvente. Ideal para lanzamientos y eventos masivos.',
                'price_per_hour' => 200000.00,
            ],
            [
                'name'           => 'Sala Cóndor',
                'type'           => 'reunión',
                'capacity'       => 8,
                'description'    => 'Sala ejecutiva privada con vista a la ciudad. Equipada con iPad de control, sillas ergonómicas y café de especialidad.',
                'price_per_hour' => 65000.00,
            ],
            [
                'name'           => 'Sala Selva',
                'type'           => 'conferencia',
                'capacity'       => 15,
                'description'    => 'Sala temática con diseño natural y plantas vivas. Pantalla interactiva, pizarrón de vidrio y estación de carga inalámbrica.',
                'price_per_hour' => 75000.00,
            ],
        ];

        $spaces = [];
        foreach ($newSpaces as $data) {
            $slug = Str::slug($data['name']);
            $existing = \DB::table('spaces')->where('slug', $slug)->first();
            $values = array_merge($data, [
                'slug'       => $slug,
                'is_active'  => 'true',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($existing) {
                \DB::table('spaces')->where('slug', $slug)->update($values);
            } else {
                \DB::table('spaces')->insert($values);
            }
            $spaces[] = Space::where('slug', $slug)->first();
        }

        $allSpaces = Space::all();
        $this->command->info($allSpaces->count() . ' espacios en total.');

        return $allSpaces->all();
    }

    private function createAvailabilities(array $spaces): void
    {
        $schedules = [
            1 => ['08:00', '18:00'],
            2 => ['08:00', '18:00'],
            3 => ['08:00', '18:00'],
            4 => ['08:00', '18:00'],
            5 => ['08:00', '18:00'],
            6 => ['09:00', '13:00'],
        ];

        foreach ($spaces as $space) {
            foreach ($schedules as $day => [$start, $end]) {
                Availability::updateOrCreate(
                    [
                        'space_id'    => $space->id,
                        'day_of_week' => $day,
                        'start_time'  => $start . ':00',
                        'end_time'    => $end . ':00',
                    ],
                    []
                );
            }
        }

        $this->command->info('Disponibilidades creadas para todos los espacios.');
    }

    private function createBlockedSlots(array $spaces): void
    {
        $blocks = [
            ['space' => 0, 'daysFromNow' => 5,  'hours' => 3, 'reason' => 'Mantenimiento programado'],
            ['space' => 1, 'daysFromNow' => 10, 'hours' => 2, 'reason' => 'Evento privado corporativo'],
            ['space' => 2, 'daysFromNow' => 7,  'hours' => 4, 'reason' => 'Prueba de equipos de sonido'],
            ['space' => 3, 'daysFromNow' => 3,  'hours' => 1, 'reason' => 'Limpieza profunda'],
            ['space' => 4, 'daysFromNow' => 14, 'hours' => 2, 'reason' => 'Capacitación del personal'],
            ['space' => 1, 'daysFromNow' => 20, 'hours' => 6, 'reason' => 'Mantenimiento de climatización'],
        ];

        foreach ($blocks as $b) {
            $space = $spaces[$b['space']];
            $start = Carbon::now()->addDays($b['daysFromNow'])->setHour(9)->setMinute(0);
            $end = $start->copy()->addHours($b['hours']);

            BlockedSlot::updateOrCreate(
                [
                    'space_id'   => $space->id,
                    'start_time' => $start,
                    'end_time'   => $end,
                ],
                ['reason' => $b['reason']]
            );
        }

        $this->command->info('Bloqueos de tiempo creados.');
    }

    private function createReservations(array $spaces, array $users): void
    {
        $faker = \Faker\Factory::create('es_ES');
        $total = 0;

        $reservationsByStatus = [
            'pendiente'  => 25,
            'confirmada' => 30,
            'finalizada' => 20,
            'cancelada'  => 12,
            'rechazada'  => 13,
        ];

        foreach ($reservationsByStatus as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $space = $faker->randomElement($spaces);
                $user = $faker->randomElement($users);

                $isPast = in_array($status, ['finalizada', 'cancelada', 'rechazada']);
                $daysOffset = $isPast
                    ? $faker->numberBetween(-60, -1)
                    : $faker->numberBetween(1, 45);

                $startHour = $faker->numberBetween(9, 16);
                $start = Carbon::now()
                    ->addDays($daysOffset)
                    ->setHour($startHour)
                    ->setMinute($faker->randomElement([0, 30]))
                    ->setSecond(0);

                $durationHours = $faker->randomElement([1, 1, 1, 2, 2, 3]);
                $end = $start->copy()->addHours($durationHours);

                $notesOptions = [
                    'pendiente' => [
                        'Requiere proyector y sonido.',
                        'Solicita café para 10 personas.',
                        'Necesito confirmar horario con el equipo.',
                        'Presentación de resultados trimestrales.',
                        'Reunión con clientes internacionales.',
                        '',
                        '',
                        '',
                    ],
                    'confirmada' => [
                        'Todo listo para la presentación.',
                        'Confirmado con catering incluido.',
                        'Equipo de sonido verificado.',
                        'Cliente confirmó asistencia.',
                        '',
                        '',
                    ],
                    'finalizada' => [
                        'Excelente sesión, todo funcionó bien.',
                        'Se reportó un micrófono con falla menor.',
                        'Sala en buen estado después del uso.',
                        'Solicitud: actualizar el software del TV.',
                        '',
                    ],
                    'cancelada' => [
                        'El cliente canceló por imprevistos.',
                        'Se reprogramó para la próxima semana.',
                        'Problemas de agenda del equipo.',
                        'Cancelación por restricciones presupuestarias.',
                        '',
                    ],
                    'rechazada' => [
                        'Horario solicitado no disponible.',
                        'Capacidad insuficiente para el evento.',
                        'El espacio no cumple con los requisitos técnicos.',
                        'Conflicto con mantenimiento programado.',
                        '',
                    ],
                ];

                $statusNotes = $notesOptions[$status] ?? [''];
                $notes = $faker->randomElement($statusNotes);

                $slug = (string) Str::uuid();

                Reservation::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'space_id'   => $space->id,
                        'user_name'  => $user->name,
                        'user_email' => $user->email,
                        'start_time' => $start,
                        'end_time'   => $end,
                        'status'     => $status,
                        'notes'      => $notes ?: null,
                        'slug'       => $slug,
                    ]
                );

                $total++;
            }
        }

        $this->command->info("$total reservas creadas.");
    }
}
