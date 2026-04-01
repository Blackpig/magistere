<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\PaymentMethod;
use BlackpigCreatif\Magistere\Enums\PaymentType;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'attendee_id' => null,
            'recorded_by' => null,
            'amount' => fake()->randomFloat(2, 50, 1000),
            'currency' => 'EUR',
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional(0.5)->bothify('REF-####-????'),
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'notes' => fake()->optional(0.3)->sentence(),
            'type' => PaymentType::Payment,
        ];
    }

    public function refund(): static
    {
        return $this->state(['type' => PaymentType::Refund]);
    }

    public function bankTransfer(): static
    {
        return $this->state([
            'method' => PaymentMethod::BankTransfer,
            'reference' => fake()->bothify('BT-########'),
        ]);
    }
}
