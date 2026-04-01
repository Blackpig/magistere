<div class="magistere-block magistere-block--eoi-form">
    @if ($workshop)
        <livewire:magistere.eoi-form :workshop="$workshop" :key="'eoi-'.$workshop->id" />
    @else
        <p>{{ __('magistere::workshop.none_available') }}</p>
    @endif
</div>
