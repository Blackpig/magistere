<div class="magistere-block magistere-block--booking-form">
    @if ($workshop)
        <livewire:magistere.booking-form :workshop="$workshop" :key="'booking-'.$workshop->id" />
    @else
        <p>{{ __('magistere::workshop.none_available') }}</p>
    @endif
</div>
