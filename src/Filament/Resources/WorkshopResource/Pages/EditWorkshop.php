<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages;

use BlackpigCreatif\Magistere\Filament\Actions\CancelWorkshopAction;
use BlackpigCreatif\Magistere\Filament\Actions\ConfirmWorkshopAction;
use BlackpigCreatif\Magistere\Filament\Actions\DuplicateWorkshopAction;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkshop extends EditRecord
{
    protected static string $resource = WorkshopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ConfirmWorkshopAction::make(),
            CancelWorkshopAction::make(),
            DuplicateWorkshopAction::make(),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
