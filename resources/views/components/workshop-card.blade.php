<div
    x-data="{ expanded: false }"
    class="magistere-workshop-card"
>
    {{-- Status badge --}}
    <div class="magistere-workshop-card__status">
        <span class="magistere-badge magistere-badge--{{ $workshop->status->value }}">
            {{ $workshop->status->getLabel() }}
        </span>
    </div>

    {{-- Title --}}
    <h3 class="magistere-workshop-card__title">{{ $displayTitle }}</h3>

    {{-- Dates --}}
    <div class="magistere-workshop-card__dates">
        <time datetime="{{ $workshop->starts_at->toDateString() }}">
            {{ $workshop->starts_at->format('d M Y') }}
        </time>
        @if ($workshop->starts_at->toDateString() !== $workshop->ends_at->toDateString())
            <span>–</span>
            <time datetime="{{ $workshop->ends_at->toDateString() }}">
                {{ $workshop->ends_at->format('d M Y') }}
            </time>
        @endif
    </div>

    {{-- Location --}}
    @if ($workshop->location)
        <div class="magistere-workshop-card__location">
            {{ $workshop->location->name }}
        </div>
    @endif

    {{-- Price --}}
    @php
        $price = $workshop->price ?? $workshop->course?->price;
    @endphp
    @if ($price !== null)
        <div class="magistere-workshop-card__price">
            {{ number_format((float) $price, 2) }} {{ $workshop->currency ?? 'EUR' }}
        </div>
    @endif

    {{-- Availability --}}
    @if ($workshop->isOpenForBooking())
        @php $spaces = $workshop->availableSpaces(); @endphp
        <div class="magistere-workshop-card__availability">
            @if ($spaces <= 3)
                {{ trans_choice('magistere::workshop.spaces_remaining', $spaces, ['count' => $spaces]) }}
            @else
                {{ __('magistere::workshop.available') }}
            @endif
        </div>
    @else
        <div class="magistere-workshop-card__availability magistere-workshop-card__availability--closed">
            {{ __('magistere::workshop.not_available') }}
        </div>
    @endif

    {{-- Description toggle --}}
    @php
        $summary = $workshop->display_summary;
        $locale  = app()->getLocale();
        $summaryText = is_array($summary)
            ? ($summary[$locale] ?? $summary['en'] ?? array_values($summary)[0] ?? null)
            : $summary;
    @endphp
    @if ($summaryText)
        <div class="magistere-workshop-card__summary">
            <button
                type="button"
                x-on:click="expanded = !expanded"
                class="magistere-workshop-card__toggle"
                :aria-expanded="expanded"
            >
                <span x-text="expanded ? '{{ __('magistere::workshop.less') }}' : '{{ __('magistere::workshop.more') }}'"></span>
            </button>
            <div x-show="expanded" x-collapse>
                <p>{{ $summaryText }}</p>
            </div>
        </div>
    @endif

    {{-- CTA slot (e.g. Book now / Register interest links) --}}
    @if (isset($cta))
        <div class="magistere-workshop-card__cta">
            {{ $cta }}
        </div>
    @endif
</div>
