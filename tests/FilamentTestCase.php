<?php

namespace BlackpigCreatif\Magistere\Tests;

use Filament\Facades\Filament;

/**
 * Base test case for tests that interact with Filament panels/resources.
 * The magistere-test panel is registered via workbench/app/Providers/FilamentServiceProvider.
 */
class FilamentTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('magistere-test'));
    }
}
