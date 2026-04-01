<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\PaymentStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $attendeeCount = fake()->numberBetween(1, 4);
        $pricePerPerson = fake()->randomFloat(2, 150, 800);
        $subtotal = round($attendeeCount * $pricePerPerson, 2);

        return [
            'workshop_id' => Workshop::factory(),
            'reference' => null, // auto-generated in boot()
            'contact_first_name' => fake()->firstName(),
            'contact_last_name' => fake()->lastName(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->optional(0.6)->phoneNumber(),
            'contact_organisation' => fake()->optional(0.3)->company(),
            'status' => BookingStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'attendee_count' => $attendeeCount,
            'subtotal' => $subtotal,
            'amount_paid' => 0,
            'currency' => 'EUR',
            'notes' => fake()->optional(0.3)->sentence(),
            'internal_notes' => null,
            'marketing_consent' => fake()->boolean(40),
            'gdpr_consent' => true,
            'confirmed_at' => null,
            'cancelled_at' => null,
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => BookingStatus::Pending]);
    }

    public function confirmed(): static
    {
        return $this->state([
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function waitlisted(): static
    {
        return $this->state(['status' => BookingStatus::Waitlisted]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'amount_paid' => $attrs['subtotal'],
            'confirmed_at' => now(),
        ]);
    }
}
