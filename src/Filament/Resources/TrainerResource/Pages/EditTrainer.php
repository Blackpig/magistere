<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\TrainerResource\Pages;

use BlackpigCreatif\Magistere\Filament\Resources\TrainerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainer extends EditRecord
{
    protected static string $resource = TrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make(), ForceDeleteAction::make()];
    }
}
