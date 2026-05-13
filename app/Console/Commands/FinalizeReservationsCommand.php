<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\UseCases\FinalizeReservationsUseCase;
use App\Domain\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class FinalizeReservationsCommand extends Command
{
    protected $signature = 'reservations:finalize
                            {--dry-run : Mostrar reservas a finalizar sin actualizar la base de datos}';

    protected $description = 'Marca como "finalizada" todas las reservas confirmadas cuyo tiempo de fin ya pasó.';

    public function __construct(
        private readonly FinalizeReservationsUseCase $finalizeUseCase,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now      = Carbon::now();
        $isDryRun = $this->option('dry-run');

        $this->info("🕐 Ejecutando a: {$now->toDateTimeString()}");
        $this->newLine();

        $reservations = $this->finalizeUseCase->getExpiredReservations();

        if ($reservations->isEmpty()) {
            $this->info('✅ No hay reservas pendientes de finalizar.');
            return self::SUCCESS;
        }

        $this->info("📋 Se encontraron <comment>{$reservations->count()}</comment> reserva(s) para finalizar:");
        $this->newLine();

        $this->table(
            ['ID', 'Slug', 'Sala', 'Usuario', 'Fin', 'Estado'],
            $reservations->map(fn (Reservation $r) => [
                $r->id,
                substr($r->slug, 0, 8) . '...',
                $r->space->name,
                $r->user_name,
                $r->end_time->toDateTimeString(),
                $r->status,
            ])->toArray()
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('⚠️  Modo --dry-run activo. No se realizaron cambios.');
            return self::SUCCESS;
        }

        if ($this->input->isInteractive()) {
            if (! $this->confirm("¿Deseas finalizar estas {$reservations->count()} reserva(s)?", true)) {
                $this->info('Operación cancelada.');
                return self::SUCCESS;
            }
        }

        $updated = $this->finalizeUseCase->finalizeExpired();

        $this->newLine();
        $this->info("✅ <comment>{$updated}</comment> reserva(s) marcadas como 'finalizada'.");

        return self::SUCCESS;
    }
}
