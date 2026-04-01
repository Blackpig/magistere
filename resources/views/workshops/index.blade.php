@extends('magistere::layouts.app')

@section('title', __('magistere::workshop.listing_title'))

@section('content')
    <div class="magistere-workshops-index">

        <header class="magistere-workshops-index__header">
            <h1>{{ __('magistere::workshop.listing_heading') }}</h1>
        </header>

        @if ($workshops->isEmpty())
            <p class="magistere-workshops-index__empty">{{ __('magistere::workshop.none_available') }}</p>
        @else
            <div class="magistere-workshops-index__grid">
                @foreach ($workshops as $workshop)
                    <x-magistere::workshop-card :workshop="$workshop">
                        <x-slot name="cta">
                            @if ($workshop->isOpenForBooking())
                                <a
                                    href="{{ route('magistere.workshops.show', $workshop->slug) }}"
                                    class="magistere-btn magistere-btn--primary"
                                >
                                    {{ __('magistere::workshop.book_now') }}
                                </a>
                            @elseif (config('magistere.features.expressions_of_interest', true))
                                <a
                                    href="{{ route('magistere.workshops.interest', $workshop->slug) }}"
                                    class="magistere-btn magistere-btn--secondary"
                                >
                                    {{ __('magistere::workshop.register_interest') }}
                                </a>
                            @endif
                        </x-slot>
                    </x-magistere::workshop-card>
                @endforeach
            </div>

            {{ $workshops->links() }}
        @endif

    </div>
@endsection
