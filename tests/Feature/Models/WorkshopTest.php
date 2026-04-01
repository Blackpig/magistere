<?php

use BlackpigCreatif\Magistere\Database\Factories\BookingFactory;
use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\LocationFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Workshop;

it('effective capacity is the course max when no overrides are set', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 10]))
        ->create(['max_capacity' => null]);

    expect($workshop->effectiveCapacity())->toBe(10);
});

it('uses the workshop max when it is more restrictive than the course max', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 20]))
        ->create(['max_capacity' => 8]);

    expect($workshop->effectiveCapacity())->toBe(8);
});

it('uses the location max when it is more restrictive', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 20]))
        ->for(LocationFactory::new()->state(['max_capacity' => 6]))
        ->create(['max_capacity' => null]);

    expect($workshop->effectiveCapacity())->toBe(6);
});

it('course max is the absolute ceiling even if workshop max is higher', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 5]))
        ->create(['max_capacity' => 50]);

    expect($workshop->effectiveCapacity())->toBe(5);
});

it('counts attendees only from confirmed and completed bookings', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 20]))
        ->create();

    BookingFactory::new()->for($workshop)->create([
        'status' => BookingStatus::Confirmed,
        'attendee_count' => 3,
    ]);

    BookingFactory::new()->for($workshop)->create([
        'status' => BookingStatus::Pending,
        'attendee_count' => 2,
    ]);

    BookingFactory::new()->for($workshop)->create([
        'status' => BookingStatus::Cancelled,
        'attendee_count' => 5,
    ]);

    expect($workshop->attendeesCount())->toBe(3);
});

it('is full when available spaces reach zero', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 2]))
        ->create(['max_capacity' => null]);

    BookingFactory::new()->for($workshop)->create([
        'status' => BookingStatus::Confirmed,
        'attendee_count' => 2,
    ]);

    expect($workshop->isFull())->toBeTrue();
    expect($workshop->availableSpaces())->toBe(0);
});

it('is open for booking when published and has spaces', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 10]))
        ->create([
            'status' => WorkshopStatus::Published,
            'registration_opens_at' => null,
            'registration_closes_at' => null,
            'max_capacity' => null,
        ]);

    expect($workshop->isOpenForBooking())->toBeTrue();
});

it('is not open for booking when status is draft', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 10]))
        ->create(['status' => WorkshopStatus::Draft]);

    expect($workshop->isOpenForBooking())->toBeFalse();
});

it('is not open for booking before registration opens', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 10]))
        ->create([
            'status' => WorkshopStatus::Published,
            'registration_opens_at' => now()->addDays(2),
        ]);

    expect($workshop->isOpenForBooking())->toBeFalse();
});

it('is not open for booking after registration closes', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 10]))
        ->create([
            'status' => WorkshopStatus::Published,
            'registration_closes_at' => now()->subDay(),
        ]);

    expect($workshop->isOpenForBooking())->toBeFalse();
});

it('display title falls back to course title when workshop title is null', function (): void {
    $course = CourseFactory::new()->create(['title' => ['en' => 'PHP Course']]);
    $workshop = WorkshopFactory::new()->for($course)->create(['title' => null]);

    $title = $workshop->display_title;
    $result = is_array($title) ? ($title['en'] ?? '') : (string) $title;

    expect($result)->toBe('PHP Course');
});

it('upcoming scope excludes past workshops', function (): void {
    WorkshopFactory::new()
        ->for(CourseFactory::new())
        ->create([
            'status' => WorkshopStatus::Published,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDays(3),
        ]);

    $upcoming = Workshop::upcoming()->get();

    expect($upcoming)->toBeEmpty();
});
