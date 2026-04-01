<?php

namespace BlackpigCreatif\Magistere\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeProviderCommand extends Command
{
    protected $signature = 'magistere:make-provider
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Publish a Magistere plugin registration stub to app/Providers';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $destination = app_path('Providers/MagistereProvider.php');

        if ($this->files->exists($destination) && ! $this->option('force')) {
            $this->components->warn('MagistereProvider already exists. Use --force to overwrite.');

            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists(dirname($destination));

        $stub = $this->files->get(__DIR__ . '/../../resources/stubs/magistere-provider.php.stub');

        $this->files->put($destination, $stub);

        $this->components->info('MagistereProvider created at app/Providers/MagistereProvider.php');
        $this->components->bulletList([
            'Add <comment>App\Providers\MagistereProvider::class</comment> to your <comment>bootstrap/providers.php</comment>',
            'Or register <comment>MagisterePlugin::make()</comment> directly in your PanelProvider\'s <comment>plugins()</comment>',
        ]);

        return self::SUCCESS;
    }
}
