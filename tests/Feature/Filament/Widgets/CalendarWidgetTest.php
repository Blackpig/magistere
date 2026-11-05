<?php

use BlackpigCreatif\Magistere\Filament\Widgets\CalendarWidget;
use Livewire\Livewire;

it('renders the calendar widget', function (): void {
    Livewire::test(CalendarWidget::class)
        ->assertOk();
});

it('returns events for a date range', function (): void {
    $events = (new CalendarWidget)->getEvents(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());

    expect($events)->toBeArray();
});
