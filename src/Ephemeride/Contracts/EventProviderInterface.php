<?php

namespace BlackpigCreatif\Magistere\Ephemeride\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Contract for Ephemeride calendar event providers.
 *
 * @phpstan-type CalendarEvent array{
 *     id: string|int,
 *     title: string,
 *     start: string,
 *     end: string,
 *     url?: string|null,
 *     color?: string|null,
 *     allDay?: bool,
 *     extendedProps?: array<string, mixed>,
 * }
 */
interface EventProviderInterface
{
    /**
     * Return calendar events within the given date range.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getEvents(Carbon $start, Carbon $end): Collection;
}
