<?php

namespace BlackpigCreatif\Magistere\View\Components;

use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\View\Component;
use Illuminate\View\View;

class WorkshopCard extends Component
{
    public string $displayTitle;

    public function __construct(public readonly Workshop $workshop)
    {
        $locale = app()->getLocale();
        $title = $workshop->display_title;

        $this->displayTitle = is_array($title)
            ? ($title[$locale] ?? $title['en'] ?? array_values($title)[0] ?? $workshop->slug)
            : (string) ($title ?? $workshop->slug);
    }

    public function render(): View
    {
        return view('magistere::components.workshop-card');
    }
}
