<?php

namespace BlackpigCreatif\Magistere\Atelier\Blocks;

use BlackpigCreatif\Magistere\Atelier\Contracts\BlockInterface;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Support\Collection;

/**
 * Atelier block: renders a paginated/filtered workshop listing.
 *
 * Implements BlockInterface as a loose contract. When the Atelier package is
 * confirmed, extend Atelier's own block base class and adapt accordingly.
 */
class WorkshopListingBlock implements BlockInterface
{
    public static function blockName(): string
    {
        return 'magistere.workshop-listing';
    }

    public static function blockLabel(): string
    {
        return 'Workshop Listing';
    }

    /**
     * Resolve workshops for rendering.
     *
     * @param  array<string, mixed>  $settings  Block settings from Atelier editor
     * @return Collection<int, Workshop>
     */
    public function resolve(array $settings = []): Collection
    {
        $limit = (int) ($settings['limit'] ?? config('magistere.per_page', 12));
        $courseId = $settings['course_id'] ?? null;
        $locationId = $settings['location_id'] ?? null;

        return Workshop::query()
            ->with(['course', 'course.category', 'location'])
            ->whereIn('status', [WorkshopStatus::Published, WorkshopStatus::Confirmed])
            ->where('starts_at', '>=', now())
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    public function render(array $settings = []): string
    {
        $workshops = $this->resolve($settings);

        return view('magistere::blocks.workshop-listing', compact('workshops', 'settings'))->render();
    }
}
