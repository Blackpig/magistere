<?php

namespace BlackpigCreatif\Magistere;

use BlackpigCreatif\Magistere\Filament\Pages\ManageSettings;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource;
use BlackpigCreatif\Magistere\Filament\Resources\CategoryResource;
use BlackpigCreatif\Magistere\Filament\Resources\CourseResource;
use BlackpigCreatif\Magistere\Filament\Resources\LocationResource;
use BlackpigCreatif\Magistere\Filament\Resources\TrainerResource;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource;
use BlackpigCreatif\Magistere\Filament\Widgets\BookingStatsWidget;
use BlackpigCreatif\Magistere\Filament\Widgets\CalendarWidget;
use BlackpigCreatif\Magistere\Filament\Widgets\UpcomingWorkshopsWidget;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;

class MagisterePlugin implements Plugin
{
    protected string $navigationGroup = 'Magistère';

    protected bool $hasCalendarWidget = false;

    protected bool $hasStatsWidget = false;

    protected bool $hasUpcomingWorkshopsWidget = false;

    protected bool $hasSettingsPage = true;

    protected bool $hasCategoryResource = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'magistere';
    }

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function withCalendarWidget(bool $condition = true): static
    {
        $this->hasCalendarWidget = $condition;

        return $this;
    }

    public function withStatsWidget(bool $condition = true): static
    {
        $this->hasStatsWidget = $condition;

        return $this;
    }

    public function withUpcomingWorkshopsWidget(bool $condition = true): static
    {
        $this->hasUpcomingWorkshopsWidget = $condition;

        return $this;
    }

    public function withSettingsPage(bool $condition = true): static
    {
        $this->hasSettingsPage = $condition;

        return $this;
    }

    public function withCategoryResource(bool $condition = true): static
    {
        $this->hasCategoryResource = $condition;

        return $this;
    }

    public function getNavigationGroup(): string
    {
        return $this->navigationGroup;
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('magistere', __DIR__ . '/../resources/dist/magistere.css'),
        ]);

        $panel->navigationGroups([
            NavigationGroup::make($this->navigationGroup),
        ]);

        $resources = [
            CourseResource::class,
            WorkshopResource::class,
            BookingResource::class,
            LocationResource::class,
            TrainerResource::class,
        ];

        if ($this->hasCategoryResource) {
            $resources[] = CategoryResource::class;
        }

        $panel->resources($resources);

        $pages = [];

        if ($this->hasSettingsPage) {
            $pages[] = ManageSettings::class;
        }

        $panel->pages($pages);

        $widgets = [];

        if ($this->hasUpcomingWorkshopsWidget) {
            $widgets[] = UpcomingWorkshopsWidget::class;
        }

        if ($this->hasCalendarWidget) {
            $widgets[] = CalendarWidget::class;
        }

        if ($this->hasStatsWidget) {
            $widgets[] = BookingStatsWidget::class;
        }

        $panel->widgets($widgets);
    }

    public function boot(Panel $panel): void {}
}
