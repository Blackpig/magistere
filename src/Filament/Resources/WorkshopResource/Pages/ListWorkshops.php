<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages;

use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkshops extends ListRecords
{
    protected static string $resource = WorkshopResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
