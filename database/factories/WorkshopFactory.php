<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Course;
use BlackpigCreatif\Magistere\Models\Location;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 week', '+6 months');
        $endsAt = (clone $startsAt)->modify('+' . fake()->numberBetween(1, 7) . ' days');
        $title = null; // inherits from course by default

        return [
            'course_id' => Course::factory(),
            'location_id' => null,
            'title' => $title,
            'slug' => Str::slug(fake()->unique()->catchPhrase()),
            'summary' => null,
            'description' => null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'registration_opens_at' => (clone $startsAt)->modify('-60 days'),
            'registration_closes_at' => (clone $startsAt)->modify('-3 days'),
            'min_capacity' => null,
            'max_capacity' => null,
            'price' => null,
            'deposit_amount' => null,
            'currency' => 'EUR',
            'status' => WorkshopStatus::Published,
            'featured_image' => null,
            'meta' => null,
            'notes' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => WorkshopStatus::Draft]);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => WorkshopStatus::Confirmed]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => WorkshopStatus::Cancelled]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => WorkshopStatus::Completed,
            'starts_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
            'ends_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
        ]);
    }

    public function withLocation(): static
    {
        return $this->state(['location_id' => Location::factory()]);
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'starts_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
            'ends_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
        ]);
    }
}
