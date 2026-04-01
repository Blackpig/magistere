<?php

namespace BlackpigCreatif\Magistere\Atelier\Blocks;

use BlackpigCreatif\Magistere\Atelier\Contracts\BlockInterface;
use BlackpigCreatif\Magistere\Models\Workshop;

/**
 * Atelier block: renders the full booking form for a given workshop.
 * Intended for workshop detail pages built in Atelier.
 */
class BookingFormBlock implements BlockInterface
{
    public static function blockName(): string
    {
        return 'magistere.booking-form';
    }

    public static function blockLabel(): string
    {
        return 'Workshop Booking Form';
    }

    /** @param  array<string, mixed>  $settings */
    public function render(array $settings = []): string
    {
        $workshopId = $settings['workshop_id'] ?? null;
        $workshop = $workshopId ? Workshop::with(['course', 'location', 'extras'])->find($workshopId) : null;

        return view('magistere::blocks.booking-form', compact('workshop', 'settings'))->render();
    }
}
