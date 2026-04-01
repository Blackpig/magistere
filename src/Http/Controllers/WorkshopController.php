<?php

namespace BlackpigCreatif\Magistere\Http\Controllers;

use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class WorkshopController extends Controller
{
    public function index(): View
    {
        $workshops = Workshop::query()
            ->with(['course', 'course.category', 'location'])
            ->whereIn('status', [WorkshopStatus::Published, WorkshopStatus::Confirmed])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->paginate(config('magistere.per_page', 12));

        return view('magistere::workshops.index', compact('workshops'));
    }

    public function show(Workshop $workshop): View | RedirectResponse
    {
        if (! in_array($workshop->status, [WorkshopStatus::Published, WorkshopStatus::Confirmed], true)) {
            abort(404);
        }

        $workshop->load(['course', 'course.category', 'location', 'trainers', 'itineraryItems', 'extras']);

        return view('magistere::workshops.show', compact('workshop'));
    }

    public function bookWithToken(Workshop $workshop, string $token): View | RedirectResponse
    {
        if (! in_array($workshop->status, [WorkshopStatus::Published, WorkshopStatus::Confirmed], true)) {
            abort(404);
        }

        $workshop->load(['course', 'course.category', 'location', 'extras']);

        return view('magistere::workshops.show', [
            'workshop' => $workshop,
            'eoiToken' => $token,
        ]);
    }

    public function interest(Workshop $workshop): View | RedirectResponse
    {
        if (! in_array($workshop->status, [WorkshopStatus::Published, WorkshopStatus::Confirmed], true)) {
            abort(404);
        }

        $workshop->load(['course', 'location']);

        return view('magistere::workshops.interest', compact('workshop'));
    }
}
