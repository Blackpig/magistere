<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\LocationResource\Pages;

use BlackpigCreatif\Magistere\Filament\Resources\LocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
