<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\TrainerStatus;
use BlackpigCreatif\Magistere\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainerFactory extends Factory
{
    protected $model = Trainer::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'bio' => ['en' => fake()->paragraphs(2, asText: true)],
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional(0.6)->phoneNumber(),
            'website' => fake()->optional(0.4)->url(),
            'featured_image' => null,
            'gallery' => null,
            'meta' => null,
            'status' => TrainerStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => TrainerStatus::Inactive]);
    }
}
