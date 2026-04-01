<?php

use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages\CreateWorkshop;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages\EditWorkshop;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages\ListWorkshops;
use BlackpigCreatif\Magistere\Models\Workshop;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs(\Workbench\App\Models\User::factory()->create());
});

it('can list workshops', function (): void {
    $workshops = WorkshopFactory::new()
        ->for(CourseFactory::new())
        ->count(3)
        ->create();

    Livewire::test(ListWorkshops::class)
        ->assertCanSeeTableRecords($workshops);
});

it('can create a workshop', function (): void {
    $course = CourseFactory::new()->create();

    Livewire::test(CreateWorkshop::class)
        ->fillForm([
            'course_id' => $course->id,
            'slug' => 'test-workshop',
            'starts_at' => now()->addDays(30)->toDateTimeString(),
            'ends_at' => now()->addDays(31)->toDateTimeString(),
            'status' => WorkshopStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Workshop::where('slug', 'test-workshop')->exists())->toBeTrue();
});

it('validates required fields on create', function (): void {
    Livewire::test(CreateWorkshop::class)
        ->fillForm([])
        ->call('create')
        ->assertHasFormErrors(['course_id', 'slug', 'starts_at', 'ends_at']);
});

it('can edit a workshop', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create([
        'status' => WorkshopStatus::Draft,
    ]);

    Livewire::test(EditWorkshop::class, ['record' => $workshop->getRouteKey()])
        ->fillForm(['status' => WorkshopStatus::Published])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($workshop->fresh()->status)->toBe(WorkshopStatus::Published);
});

it('can soft delete a workshop', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(EditWorkshop::class, ['record' => $workshop->getRouteKey()])
        ->callAction(\Filament\Actions\DeleteAction::class);

    expect($workshop->fresh()->trashed())->toBeTrue();
});

it('can filter workshops by status', function (): void {
    WorkshopFactory::new()->for(CourseFactory::new())->create(['status' => WorkshopStatus::Published]);
    WorkshopFactory::new()->for(CourseFactory::new())->create(['status' => WorkshopStatus::Draft]);

    Livewire::test(ListWorkshops::class)
        ->filterTable('status', WorkshopStatus::Published->value)
        ->assertCountTableRecords(1);
});
