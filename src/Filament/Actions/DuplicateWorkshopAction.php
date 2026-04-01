<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Workshop;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class DuplicateWorkshopAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'duplicateWorkshop';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Duplicate')
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->modalHeading('Duplicate Workshop')
            ->modalDescription('Creates a copy of this workshop with new dates. Trainers, itinerary, and extras will be copied.')
            ->form([
                TextInput::make('slug')
                    ->label('New Slug')
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('starts_at')
                    ->label('New Start Date')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('New End Date')
                    ->required(),
            ])
            ->action(function (Workshop $record, array $data): void {
                /** @var Workshop $copy */
                $copy = $record->replicate(['status']);
                $copy->slug = $data['slug'];
                $copy->starts_at = $data['starts_at'];
                $copy->ends_at = $data['ends_at'];
                $copy->status = WorkshopStatus::Draft;
                $copy->save();

                // Copy trainers with pivot data
                $record->trainers->each(function ($trainer) use ($copy): void {
                    $copy->trainers()->attach($trainer->id, [
                        'role' => $trainer->pivot->role,
                        'sort_order' => $trainer->pivot->sort_order,
                    ]);
                });

                // Copy itinerary items
                $record->itineraryItems->each(function ($item) use ($copy): void {
                    $copy->itineraryItems()->create($item->only([
                        'day',
                        'sort_order',
                        'start_time',
                        'end_time',
                        'title',
                        'description',
                    ]));
                });

                // Copy extras
                $record->extras->each(function ($extra) use ($copy): void {
                    $copy->extras()->create($extra->only([
                        'name',
                        'description',
                        'price',
                        'per',
                        'is_required',
                        'sort_order',
                    ]));
                });

                Notification::make()
                    ->title('Workshop duplicated')
                    ->body('A draft copy has been created.')
                    ->success()
                    ->send();
            });
    }
}
