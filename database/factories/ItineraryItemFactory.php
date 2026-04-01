<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Models\ItineraryItem;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItineraryItemFactory extends Factory
{
    protected $model = ItineraryItem::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 17);
        $endHour = $startHour + fake()->numberBetween(1, 3);

        return [
            'workshop_id' => Workshop::factory(),
            'day' => fake()->numberBetween(1, 5),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', min($endHour, 21)),
            'title' => ['en' => fake()->sentence(4)],
            'description' => ['en' => fake()->optional(0.5)->paragraph()],
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
