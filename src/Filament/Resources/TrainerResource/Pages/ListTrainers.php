<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\TrainerResource\Pages;

use BlackpigCreatif\Magistere\Filament\Resources\TrainerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainers extends ListRecords
{
    protected static string $resource = TrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
