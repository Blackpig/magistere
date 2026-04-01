<?php

namespace BlackpigCreatif\Magistere\Filament\Widgets;

use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Workshop;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingWorkshopsWidget extends TableWidget
{
    protected static ?int $sort = 10;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Upcoming Workshops')
            ->query(
                Workshop::query()
                    ->with(['course', 'location'])
                    ->whereIn('status', [WorkshopStatus::Published, WorkshopStatus::Confirmed])
                    ->where('starts_at', '>=', now())
                    ->orderBy('starts_at')
                    ->limit(10),
            )
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '—')
                        : (string) ($state ?? '—')),

                TextColumn::make('starts_at')
                    ->label('Date')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('location.name')
                    ->label('Location')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('bookings_count')
                    ->label('Bookings')
                    ->counts('bookings'),

                TextColumn::make('available_spaces')
                    ->label('Spaces')
                    ->state(fn (Workshop $record): int => $record->availableSpaces()),
            ])
            ->paginated(false);
    }
}
