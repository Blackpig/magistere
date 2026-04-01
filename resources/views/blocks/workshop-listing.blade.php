<div class="magistere-block magistere-block--workshop-listing">
    @forelse ($workshops as $workshop)
        <x-magistere::workshop-card :workshop="$workshop">
            <x-slot name="cta">
                @if ($workshop->isOpenForBooking())
                    <a href="{{ route('magistere.workshops.show', $workshop->slug) }}" class="magistere-btn magistere-btn--primary">
                        {{ __('magistere::workshop.book_now') }}
                    </a>
                @elseif (config('magistere.features.expressions_of_interest', true))
                    <a href="{{ route('magistere.workshops.interest', $workshop->slug) }}" class="magistere-btn magistere-btn--secondary">
                        {{ __('magistere::workshop.register_interest') }}
                    </a>
                @endif
            </x-slot>
        </x-magistere::workshop-card>
    @empty
        <p>{{ __('magistere::workshop.none_available') }}</p>
    @endforelse
</div>
