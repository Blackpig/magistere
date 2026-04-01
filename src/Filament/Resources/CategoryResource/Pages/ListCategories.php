<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\CategoryResource\Pages;

use BlackpigCreatif\Magistere\Filament\Resources\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
