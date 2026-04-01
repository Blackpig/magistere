<?php

namespace BlackpigCreatif\Magistere\Filament\Widgets;

use BlackpigCreatif\Magistere\Ephemeride\WorkshopProvider;
use Carbon\Carbon;
use Filament\Widgets\Widget;

/**
 * Calendar widget for the Filament dashboard.
 *
 * Renders workshop events using the WorkshopProvider. If a full-featured
 * Filament calendar widget package is installed (e.g. saade/filament-fullcalendar),
 * extend its base class instead and override getEvents().
 */
class CalendarWidget extends Widget
{
    protected static string $view = 'magistere::widgets.calendar';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(string $start, string $end): array
    {
        return app(WorkshopProvider::class)
            ->getEvents(Carbon::parse($start), Carbon::parse($end))
            ->all();
    }
}
