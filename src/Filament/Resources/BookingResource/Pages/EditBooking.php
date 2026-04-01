<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages;

use BlackpigCreatif\Magistere\Filament\Actions\ConfirmBookingAction;
use BlackpigCreatif\Magistere\Filament\Actions\MoveToWaitlistAction;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ConfirmBookingAction::make(),
            MoveToWaitlistAction::make(),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
