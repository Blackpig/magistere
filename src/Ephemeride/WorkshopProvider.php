<?php

namespace BlackpigCreatif\Magistere\Ephemeride;

use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Ephemeride\Contracts\EventProviderInterface;
use BlackpigCreatif\Magistere\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkshopProvider implements EventProviderInterface
{
    /**
     * Return workshops in the given date range as calendar events.
     *
     * Statuses are driven by config('magistere.ephemeride.workshop_statuses').
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getEvents(Carbon $start, Carbon $end): Collection
    {
        $statuses = array_map(
            fn (string $value): WorkshopStatus => WorkshopStatus::from($value),
            config('magistere.ephemeride.workshop_statuses', ['published', 'confirmed']),
        );

        return Workshop::query()
            ->with(['course', 'location'])
            ->whereIn('status', $statuses)
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('starts_at', [$start, $end])
                    ->orWhereBetween('ends_at', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end): void {
                        $q->where('starts_at', '<=', $start)
                            ->where('ends_at', '>=', $end);
                    });
            })
            ->get()
            ->map(fn (Workshop $workshop): array => $this->toEvent($workshop));
    }

    /** @return array<string, mixed> */
    protected function toEvent(Workshop $workshop): array
    {
        $locale = app()->getLocale();
        $title = $workshop->display_title;

        if (is_array($title)) {
            $title = $title[$locale] ?? $title['en'] ?? array_values($title)[0] ?? $workshop->slug;
        }

        $color = $workshop->course?->category?->colour ?? null;

        return [
            'id' => $workshop->id,
            'title' => (string) $title,
            'start' => $workshop->starts_at->toIso8601String(),
            'end' => $workshop->ends_at->toIso8601String(),
            'color' => $color,
            'allDay' => false,
            'extendedProps' => [
                'status' => $workshop->status->value,
                'location' => $workshop->location?->name,
                'availableSpaces' => $workshop->availableSpaces(),
            ],
        ];
    }
}
