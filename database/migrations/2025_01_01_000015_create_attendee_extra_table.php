<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_extra', function (Blueprint $table): void {
            $table->foreignId('attendee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extra_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);

            $table->primary(['attendee_id', 'extra_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_extra');
    }
};
