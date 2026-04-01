<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->string('level')->default('all');
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedSmallInteger('duration_hours')->nullable();
            $table->unsignedInteger('min_capacity')->nullable();
            $table->unsignedInteger('max_capacity');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->char('currency', 3)->default('EUR');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('meta')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
