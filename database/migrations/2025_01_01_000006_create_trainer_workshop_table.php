<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_workshop', function (Blueprint $table): void {
            $table->foreignId('trainer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['trainer_id', 'workshop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_workshop');
    }
};
