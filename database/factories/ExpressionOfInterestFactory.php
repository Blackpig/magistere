<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\EoiSource;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpressionOfInterestFactory extends Factory
{
    protected $model = ExpressionOfInterest::class;

    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'course_id' => null,
            'converted_booking_id' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional(0.5)->phoneNumber(),
            'attendee_count' => fake()->numberBetween(1, 4),
            'message' => fake()->optional(0.4)->paragraph(),
            'source' => EoiSource::Interest,
            'status' => EoiStatus::New,
            'token' => null, // auto-generated in boot()
            'token_expires_at' => null,
            'notified_at' => null,
        ];
    }

    public function asNew(): static
    {
        return $this->state(['status' => EoiStatus::New]);
    }

    public function interest(): static
    {
        return $this->state(['source' => EoiSource::Interest]);
    }

    public function waitlist(): static
    {
        return $this->state(['source' => EoiSource::Waitlist]);
    }

    public function contacted(): static
    {
        return $this->state([
            'status' => EoiStatus::Contacted,
            'notified_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'token_expires_at' => now()->addHours(config('magistere.booking.token_expiry_hours', 72)),
        ]);
    }

    public function converted(): static
    {
        return $this->state(['status' => EoiStatus::Converted]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => EoiStatus::Contacted,
            'notified_at' => now()->subDays(5),
            'token_expires_at' => now()->subDays(2),
        ]);
    }
}
