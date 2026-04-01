<?php

namespace BlackpigCreatif\Magistere\Filament\Concerns;

trait BelongsToMagistereNavigationGroup
{
    public static function getNavigationGroup(): ?string
    {
        return 'Magistère';
    }
}
