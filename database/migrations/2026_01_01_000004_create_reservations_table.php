<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')
                  ->constrained('spaces')
                  ->restrictOnDelete();
            $table->string('user_name');
            $table->string('user_email');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', [
                'pendiente',
                'confirmada',
                'rechazada',
                'cancelada',
                'finalizada',
            ])->default('pendiente');
            $table->text('notes')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index(['space_id', 'start_time', 'end_time'], 'idx_reservations_range');
            $table->index('status');
            $table->index('user_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
