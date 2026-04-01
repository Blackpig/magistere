<?php

use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\EoiSource;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Livewire\EoiForm;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use Livewire\Livewire;

it('renders the eoi form', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(EoiForm::class, ['workshop' => $workshop])
        ->assertStatus(200)
        ->assertSet('submitted', false);
});

it('creates an expression of interest on submit', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(EoiForm::class, ['workshop' => $workshop])
        ->set('firstName', 'Alice')
        ->set('lastName', 'Dupont')
        ->set('email', 'alice@example.com')
        ->set('attendeeCount', 2)
        ->call('submit')
        ->assertSet('submitted', true);

    expect(ExpressionOfInterest::where('email', 'alice@example.com')->exists())->toBeTrue();

    $eoi = ExpressionOfInterest::where('email', 'alice@example.com')->first();
    expect($eoi->workshop_id)->toBe($workshop->id);
    expect($eoi->attendee_count)->toBe(2);
    expect($eoi->status)->toBe(EoiStatus::New);
    expect($eoi->source)->toBe(EoiSource::Interest);
});

it('validates required fields', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(EoiForm::class, ['workshop' => $workshop])
        ->call('submit')
        ->assertHasErrors(['firstName', 'lastName', 'email']);
});

it('validates email format', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(EoiForm::class, ['workshop' => $workshop])
        ->set('firstName', 'Alice')
        ->set('lastName', 'Dupont')
        ->set('email', 'not-an-email')
        ->set('attendeeCount', 1)
        ->call('submit')
        ->assertHasErrors(['email']);
});

it('respects the source prop', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(EoiForm::class, ['workshop' => $workshop, 'source' => EoiSource::Waitlist->value])
        ->set('firstName', 'Bob')
        ->set('lastName', 'Martin')
        ->set('email', 'bob@example.com')
        ->set('attendeeCount', 1)
        ->call('submit');

    $eoi = ExpressionOfInterest::where('email', 'bob@example.com')->first();
    expect($eoi->source)->toBe(EoiSource::Waitlist);
});
