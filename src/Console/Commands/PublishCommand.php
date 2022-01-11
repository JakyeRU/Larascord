<?php

namespace Jakyeru\Larascord\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larascord:publish
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

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
        // Inform the user that the command is starting.
        $this->info('Publishing Larascord\'s settings...');

        // Publish the configuration file.
        try {
            shell_exec('php artisan vendor:publish --provider="Jakyeru\Larascord\LarascordServiceProvider" --tag=config');
        } catch (\Exception $e) {
            $this->error('Could not publish the configuration file.');
            $this->error($e->getMessage());
        }

        // Inform the user that the command has finished.
        $this->info('Larascord\'s settings published successfully.');
    }
}