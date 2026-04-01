<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Models\Attendee;
use BlackpigCreatif\Magistere\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendeeFactory extends Factory
{
    protected $model = Attendee::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'phone' => fake()->optional(0.4)->phoneNumber(),
            'dietary_requirements' => fake()->optional(0.2)->sentence(),
            'accessibility_requirements' => fake()->optional(0.1)->sentence(),
            'notes' => null,
            'is_primary_contact' => false,
            'checked_in_at' => null,
        ];
    }

    public function primaryContact(): static
    {
        return $this->state(['is_primary_contact' => true]);
    }

    public function checkedIn(): static
    {
        return $this->state(['checked_in_at' => now()]);
    }
}
