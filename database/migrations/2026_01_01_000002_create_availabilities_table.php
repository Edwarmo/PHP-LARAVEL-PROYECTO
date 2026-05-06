<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')
                  ->constrained('spaces')
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Dom, 6=Sáb
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(
                ['space_id', 'day_of_week', 'start_time', 'end_time'],
                'unique_availability'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
