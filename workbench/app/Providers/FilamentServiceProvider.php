<?php

namespace Workbench\App\Providers;

use BlackpigCreatif\Magistere\MagisterePlugin;
use Filament\Panel;
use Filament\PanelProvider;

class FilamentServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('magistere-test')
            ->path('admin')
            ->plugins([MagisterePlugin::make()])
            ->authGuard('web');
    }
}
