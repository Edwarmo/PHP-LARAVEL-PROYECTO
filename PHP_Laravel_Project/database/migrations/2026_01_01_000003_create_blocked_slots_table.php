<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')
                  ->constrained('spaces')
                  ->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(
                ['space_id', 'start_time', 'end_time'],
                'idx_blocked_slots_range'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_slots');
    }
};
