<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * reservations:finalize
 *
 * Cambia el estado de todas las reservas "confirmadas"
 * cuyo end_time sea anterior al momento actual a "finalizada".
 *
 * Uso:
 *   php artisan reservations:finalize
 *   php artisan reservations:finalize --dry-run   (solo muestra, no actualiza)
 *
 * Recomendado en el scheduler:
 *   $schedule->command('reservations:finalize')->everyFifteenMinutes();
 */
final class FinalizeReservationsCommand extends Command
{
    protected $signature = 'reservations:finalize
                            {--dry-run : Mostrar reservas a finalizar sin actualizar la base de datos}';

    protected $description = 'Marca como "finalizada" todas las reservas confirmadas cuyo tiempo de fin ya pasó.';

    public function handle(): int
    {
        $now      = Carbon::now();
        $isDryRun = $this->option('dry-run');

        $this->info("🕐 Ejecutando a: {$now->toDateTimeString()}");
        $this->newLine();

        // Buscar reservas confirmadas que ya terminaron
        $reservations = Reservation::with('space')
            ->where('status', Reservation::STATUS_CONFIRMADA)
            ->where('end_time', '<', $now)
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('✅ No hay reservas pendientes de finalizar.');
            return self::SUCCESS;
        }

        $this->info("📋 Se encontraron <comment>{$reservations->count()}</comment> reserva(s) para finalizar:");
        $this->newLine();

        // Mostrar tabla de reservas afectadas
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

        // Modo dry-run: no actualizar
        if ($isDryRun) {
            $this->newLine();
            $this->warn('⚠️  Modo --dry-run activo. No se realizaron cambios.');
            return self::SUCCESS;
        }

        // Confirmación en entornos interactivos (no en scheduler)
        if ($this->input->isInteractive()) {
            if (! $this->confirm("¿Deseas finalizar estas {$reservations->count()} reserva(s)?", true)) {
                $this->info('Operación cancelada.');
                return self::SUCCESS;
            }
        }

        // Actualizar en lote
        $updated = Reservation::where('status', Reservation::STATUS_CONFIRMADA)
            ->where('end_time', '<', $now)
            ->update(['status' => Reservation::STATUS_FINALIZADA]);

        $this->newLine();
        $this->info("✅ <comment>{$updated}</comment> reserva(s) marcadas como 'finalizada'.");

        return self::SUCCESS;
    }
}
