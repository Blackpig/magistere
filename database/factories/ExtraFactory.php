<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\ExtraPer;
use BlackpigCreatif\Magistere\Models\Extra;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExtraFactory extends Factory
{
    protected $model = Extra::class;

    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'title' => ['en' => fake()->words(3, asText: true)],
            'description' => ['en' => fake()->optional(0.5)->sentence()],
            'price' => fake()->randomFloat(2, 0, 150),
            'currency' => 'EUR',
            'capacity' => fake()->optional(0.4)->numberBetween(5, 50),
            'per' => fake()->randomElement(ExtraPer::cases()),
            'is_required' => false,
            'sort_order' => fake()->numberBetween(0, 20),
            'status' => 'active',
        ];
    }

    public function required(): static
    {
        return $this->state(['is_required' => true]);
    }

    public function free(): static
    {
        return $this->state(['price' => 0]);
    }

    public function perAttendee(): static
    {
        return $this->state(['per' => ExtraPer::Attendee]);
    }
}
