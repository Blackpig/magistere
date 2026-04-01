@extends('magistere::layouts.app')

@php
    $locale      = app()->getLocale();
    $title       = $workshop->display_title;
    $displayTitle = is_array($title)
        ? ($title[$locale] ?? $title['en'] ?? array_values($title)[0] ?? $workshop->slug)
        : (string) ($title ?? $workshop->slug);

    $description = $workshop->display_summary;
    $displayDesc  = is_array($description)
        ? ($description[$locale] ?? $description['en'] ?? array_values($description)[0] ?? null)
        : $description;

    $eoiToken = $eoiToken ?? null;
@endphp

@section('title', $displayTitle)

@section('content')
    <div class="magistere-workshop-detail">

        {{-- Header --}}
        <header class="magistere-workshop-detail__header">
            <span class="magistere-badge magistere-badge--{{ $workshop->status->value }}">
                {{ $workshop->status->getLabel() }}
            </span>
            <h1>{{ $displayTitle }}</h1>

            <div class="magistere-workshop-detail__meta">
                <span class="magistere-workshop-detail__dates">
                    {{ $workshop->starts_at->format('d M Y') }}
                    @if ($workshop->starts_at->toDateString() !== $workshop->ends_at->toDateString())
                        – {{ $workshop->ends_at->format('d M Y') }}
                    @endif
                </span>

                @if ($workshop->location)
                    <span class="magistere-workshop-detail__location">
                        {{ $workshop->location->name }}
                    </span>
                @endif

                @php $price = $workshop->price ?? $workshop->course?->price; @endphp
                @if ($price)
                    <span class="magistere-workshop-detail__price">
                        {{ number_format((float) $price, 2) }} {{ $workshop->currency ?? 'EUR' }}
                    </span>
                @endif
            </div>
        </header>

        {{-- Description --}}
        @if ($displayDesc)
            <div class="magistere-workshop-detail__description">
                {!! nl2br(e($displayDesc)) !!}
            </div>
        @endif

        {{-- Trainers --}}
        @if ($workshop->trainers->isNotEmpty())
            <section class="magistere-workshop-detail__trainers">
                <h2>{{ __('magistere::workshop.trainers_heading') }}</h2>
                <ul>
                    @foreach ($workshop->trainers as $trainer)
                        <li>{{ $trainer->first_name }} {{ $trainer->last_name }}
                            @if ($trainer->pivot->role)
                                — {{ $trainer->pivot->role }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Itinerary --}}
        @if ($workshop->itineraryItems->isNotEmpty() && config('magistere.features.itinerary', true))
            <section class="magistere-workshop-detail__itinerary">
                <h2>{{ __('magistere::workshop.itinerary_heading') }}</h2>
                @foreach ($workshop->itineraryItems->groupBy('day') as $day => $items)
                    <div class="magistere-itinerary-day">
                        <h3>{{ __('magistere::workshop.day_n', ['n' => $day]) }}</h3>
                        <ul>
                            @foreach ($items as $item)
                                @php
                                    $itemTitle = is_array($item->title)
                                        ? ($item->title[$locale] ?? $item->title['en'] ?? array_values($item->title)[0] ?? '')
                                        : (string) $item->title;
                                @endphp
                                <li>
                                    @if ($item->start_time)
                                        <time>{{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}</time>
                                    @endif
                                    {{ $itemTitle }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Booking / EOI form --}}
        <section class="magistere-workshop-detail__action">
            @if ($workshop->isOpenForBooking())
                <h2>{{ __('magistere::booking.book_heading') }}</h2>
                <livewire:magistere.booking-form
                    :workshop="$workshop"
                    :eoi-token="$eoiToken"
                    :key="'booking-'.$workshop->id.($eoiToken ?? '')"
                />
            @elseif (config('magistere.features.expressions_of_interest', true))
                <h2>{{ __('magistere::eoi.register_heading') }}</h2>
                <p>{{ __('magistere::eoi.register_intro') }}</p>
                <livewire:magistere.eoi-form
                    :workshop="$workshop"
                    :key="'eoi-'.$workshop->id"
                />
            @else
                <p class="magistere-workshop-detail__closed">{{ __('magistere::workshop.bookings_closed') }}</p>
            @endif
        </section>

    </div>
@endsection
