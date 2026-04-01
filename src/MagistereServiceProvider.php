<?php

namespace BlackpigCreatif\Magistere;

use BlackpigCreatif\Magistere\Atelier\Blocks\BookingFormBlock;
use BlackpigCreatif\Magistere\Atelier\Blocks\EoiFormBlock;
use BlackpigCreatif\Magistere\Atelier\Blocks\WorkshopListingBlock;
use BlackpigCreatif\Magistere\Commands\MakeProviderCommand;
use BlackpigCreatif\Magistere\Ephemeride\WorkshopProvider;
use BlackpigCreatif\Magistere\Livewire\BookingForm;
use BlackpigCreatif\Magistere\Livewire\EoiForm;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MagistereServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('magistere')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoutes(['web'])
            ->discoversMigrations()
            ->hasCommands([
                MakeProviderCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->registerLivewireComponents();
        $this->conditionallyRegisterEphemeride();
        $this->conditionallyRegisterAtelier();
    }

    protected function registerLivewireComponents(): void
    {
        Blade::componentNamespace('BlackpigCreatif\\Magistere\\View\\Components', 'magistere');

        Livewire::component('magistere.eoi-form', EoiForm::class);
        Livewire::component('magistere.booking-form', BookingForm::class);
    }

    protected function conditionallyRegisterEphemeride(): void
    {
        // Bind our WorkshopProvider into the container so host apps and the
        // Ephemeride calendar package can resolve it via the config key.
        $this->app->bind(
            config('magistere.ephemeride.provider'),
            WorkshopProvider::class,
        );
    }

    protected function conditionallyRegisterAtelier(): void
    {
        // Register blocks only when the Atelier page builder is installed.
        // Atelier's block registry class is resolved from the container;
        // adapt this binding to match Atelier's actual registration API.
        if (! class_exists(\Blackpig\Atelier\Facades\Atelier::class)) {
            return;
        }

        \Blackpig\Atelier\Facades\Atelier::registerBlocks([
            WorkshopListingBlock::class,
            EoiFormBlock::class,
            BookingFormBlock::class,
        ]);
    }
}
