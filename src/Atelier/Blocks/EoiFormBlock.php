<?php

namespace BlackpigCreatif\Magistere\Atelier\Blocks;

use BlackpigCreatif\Magistere\Atelier\Contracts\BlockInterface;
use BlackpigCreatif\Magistere\Models\Workshop;

/**
 * Atelier block: renders an Expression of Interest form for a given workshop.
 */
class EoiFormBlock implements BlockInterface
{
    public static function blockName(): string
    {
        return 'magistere.eoi-form';
    }

    public static function blockLabel(): string
    {
        return 'Expression of Interest Form';
    }

    /** @param  array<string, mixed>  $settings */
    public function render(array $settings = []): string
    {
        $workshopId = $settings['workshop_id'] ?? null;
        $workshop = $workshopId ? Workshop::with(['course', 'location'])->find($workshopId) : null;

        return view('magistere::blocks.eoi-form', compact('workshop', 'settings'))->render();
    }
}
