<?php

namespace Jakyeru\Larascord\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larascord:publish
                            {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use this command to publish Larascord\'s settings.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Checking if the configuration has already been published.
        if (file_exists(config_path('larascord.php')) && !$this->option('force')) {
            $this->error('The configuration file has already been published.');
            $this->error('If you want to overwrite the existing file, use the --force option.');

            return;
        }

        // Checking if the command should be run forcefully.
        if ($this->option('force')) {
            $this->warn('This command is running in force mode. Any existing configuration file will be overwritten.');
        }

        // Publish the configuration file.
        try {
            shell_exec('php artisan vendor:publish --provider="Jakyeru\Larascord\LarascordServiceProvider" --tag=config' . ($this->option('force') ? ' --force' : ''));
        } catch (\Exception $e) {
            $this->error('Could not publish the configuration file.');
            $this->error($e->getMessage());
        }

        // Inform the user that the command has finished.
        $this->info('Larascord\'s settings have been published successfully.');
    }
}