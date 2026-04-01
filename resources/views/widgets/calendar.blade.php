<x-filament-widgets::widget class="fi-wi-magistere-calendar">
    <x-filament::section>
        <x-slot name="heading">Workshop Calendar</x-slot>

        {{--
            This widget provides event data via getEvents().
            To render a full interactive calendar, install a Filament calendar
            package (e.g. saade/filament-fullcalendar) and extend its base class
            in CalendarWidget instead of Filament\Widgets\Widget.
        --}}
        <div
            id="magistere-calendar"
            x-data="{
                events: [],
                init() {
                    const now   = new Date();
                    const start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString();
                    const end   = new Date(now.getFullYear(), now.getMonth() + 2, 0).toISOString();

                    $wire.getEvents(start, end).then(data => { this.events = data; });
                }
            }"
        >
            <template x-if="events.length === 0">
                <p class="text-sm text-gray-500">No upcoming workshops this period.</p>
            </template>
            <ul x-show="events.length > 0" class="divide-y divide-gray-200">
                <template x-for="event in events" :key="event.id">
                    <li class="py-2 flex justify-between items-center">
                        <span x-text="event.title" class="font-medium"></span>
                        <span x-text="new Date(event.start).toLocaleDateString()" class="text-sm text-gray-500"></span>
                    </li>
                </template>
            </ul>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
