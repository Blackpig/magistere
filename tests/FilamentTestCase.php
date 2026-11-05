<?php

namespace BlackpigCreatif\Magistere\Tests;

use Filament\Facades\Filament;
use Workbench\App\Providers\FilamentServiceProvider as WorkbenchPanelProvider;

/**
 * Base test case for tests that interact with Filament panels/resources.
 *
 * The magistere-test panel is registered here rather than through testbench.yaml,
 * which is gitignored and so missing in CI.
 */
class FilamentTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('magistere-test'));
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            WorkbenchPanelProvider::class,
        ]);
    }
}
