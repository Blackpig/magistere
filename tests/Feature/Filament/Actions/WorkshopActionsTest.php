<?php

use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Filament\Actions\CancelWorkshopAction;
use BlackpigCreatif\Magistere\Filament\Actions\ConfirmWorkshopAction;
use BlackpigCreatif\Magistere\Filament\Actions\DuplicateWorkshopAction;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages\EditWorkshop;
use BlackpigCreatif\Magistere\Models\Workshop;
use Livewire\Livewire;
use Workbench\App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('can confirm a published workshop', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new())
        ->create(['status' => WorkshopStatus::Published]);

    Livewire::test(EditWorkshop::class, ['record' => $workshop->getRouteKey()])
        ->callAction(ConfirmWorkshopAction::class);

    expect($workshop->fresh()->status)->toBe(WorkshopStatus::Confirmed);
});

it('confirm action is hidden for draft workshops', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new())
        ->create(['status' => WorkshopStatus::Draft]);

    Livewire::test(EditWorkshop::class, ['record' => $workshop->getRouteKey()])
        ->assertActionHidden(ConfirmWorkshopAction::class);
});

it('can cancel a published workshop', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new())
        ->create(['status' => WorkshopStatus::Published]);

    Livewire::test(EditWorkshop::class, ['record' => $workshop->getRouteKey()])
        ->callAction(CancelWorkshopAction::class);

    expect($workshop->fresh()->status)->toBe(WorkshopStatus::Cancelled);
});

it('can duplicate a workshop', function (): void {
    $workshop = WorkshopFactory::new()
        ->for(CourseFactory::new())
        ->create(['status' => WorkshopStatus::Published, 'slug' => 'original-workshop']);

    $newStart = now()->addDays(60)->toDateTimeString();
    $newEnd = now()->addDays(61)->toDateTimeString();

    Livewire::test(EditWorkshop::class, ['record' => $workshop->getRouteKey()])
        ->callAction(DuplicateWorkshopAction::class, data: [
            'slug' => 'duplicate-workshop',
            'starts_at' => $newStart,
            'ends_at' => $newEnd,
        ]);

    expect(Workshop::where('slug', 'duplicate-workshop')->exists())->toBeTrue();

    $copy = Workshop::where('slug', 'duplicate-workshop')->first();
    expect($copy->status)->toBe(WorkshopStatus::Draft);
    expect($copy->course_id)->toBe($workshop->course_id);
});
