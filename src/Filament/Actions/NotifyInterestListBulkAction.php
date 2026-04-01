<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use BlackpigCreatif\Magistere\Notifications\EoiInterestListNotification;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotifyInterestListBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'notifyInterestList';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Notify Selected')
            ->icon('heroicon-o-envelope')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Notify Interest List')
            ->modalDescription('This will send a notification to all selected expressions of interest.')
            ->action(function (Collection $records): void {
                $count = 0;

                /** @var ExpressionOfInterest $record */
                foreach ($records as $record) {
                    if (! $record->workshop) {
                        continue;
                    }

                    \Illuminate\Support\Facades\Notification::route('mail', $record->email)
                        ->notify(new EoiInterestListNotification($record->workshop));

                    $record->update([
                        'status' => EoiStatus::Contacted,
                        'notified_at' => now(),
                    ]);

                    $count++;
                }

                Notification::make()
                    ->title("{$count} notification(s) sent")
                    ->success()
                    ->send();

                $this->deselectAllTableRecords();
            });
    }
}
