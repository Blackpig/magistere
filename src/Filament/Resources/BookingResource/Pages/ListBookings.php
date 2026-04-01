<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages;

use BlackpigCreatif\Magistere\Filament\Resources\BookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
