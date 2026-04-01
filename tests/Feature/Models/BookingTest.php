<?php

use BlackpigCreatif\Magistere\Database\Factories\BookingFactory;
use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\PaymentFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\PaymentMethod;
use BlackpigCreatif\Magistere\Enums\PaymentStatus;
use BlackpigCreatif\Magistere\Enums\PaymentType;
use BlackpigCreatif\Magistere\Models\Booking;

it('generates a reference on creation', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create();

    expect($booking->reference)->toMatch('/^MAG-\d{4}-\d{4}$/');
});

it('reference is unique per booking', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    $b1 = BookingFactory::new()->for($workshop)->create();
    $b2 = BookingFactory::new()->for($workshop)->create();

    expect($b1->reference)->not->toBe($b2->reference);
});

it('contact full name accessor combines first and last name', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create([
            'contact_first_name' => 'Jane',
            'contact_last_name' => 'Smith',
        ]);

    expect($booking->contact_full_name)->toBe('Jane Smith');
});

it('payment status is unpaid when no payments recorded', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['subtotal' => 100]);

    $booking->recalculatePaymentStatus();
    $booking->refresh();

    expect($booking->payment_status)->toBe(PaymentStatus::Unpaid);
    expect((float) $booking->amount_paid)->toBe(0.0);
});

it('payment status becomes paid when full amount is received', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['subtotal' => 100]);

    PaymentFactory::new()->for($booking)->create([
        'amount' => 100,
        'type' => PaymentType::Payment,
        'method' => PaymentMethod::BankTransfer,
    ]);

    $booking->refresh();

    expect($booking->payment_status)->toBe(PaymentStatus::Paid);
});

it('payment status becomes deposit received when deposit threshold is met', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['subtotal' => 100]);

    PaymentFactory::new()->for($booking)->create([
        'amount' => 25,
        'type' => PaymentType::Payment,
        'method' => PaymentMethod::BankTransfer,
    ]);

    $booking->refresh();

    expect($booking->payment_status)->toBe(PaymentStatus::DepositReceived);
});

it('payment status becomes overpaid when amount exceeds subtotal', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['subtotal' => 100]);

    PaymentFactory::new()->for($booking)->create([
        'amount' => 150,
        'type' => PaymentType::Payment,
        'method' => PaymentMethod::BankTransfer,
    ]);

    $booking->refresh();

    expect($booking->payment_status)->toBe(PaymentStatus::Overpaid);
});

it('payment status recalculates when a payment is deleted', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['subtotal' => 100]);

    $payment = PaymentFactory::new()->for($booking)->create([
        'amount' => 100,
        'type' => PaymentType::Payment,
        'method' => PaymentMethod::BankTransfer,
    ]);

    expect($booking->fresh()->payment_status)->toBe(PaymentStatus::Paid);

    $payment->delete();

    expect($booking->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

it('pending scope returns only pending bookings', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();
    BookingFactory::new()->for($workshop)->pending()->create();
    BookingFactory::new()->for($workshop)->confirmed()->create();

    expect(Booking::pending()->count())->toBe(1);
});
