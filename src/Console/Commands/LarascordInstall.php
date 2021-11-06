<?php

namespace Jakyeru\Larascord\Console\Commands;

use Illuminate\Console\Command;

class LarascordInstall extends Command
{
    protected $name = 'larascord:install';

    protected $description = 'Use this command to install Larascord.';

    public function handle()
    {
        $this->info('Larascord is now installed.');
    }
}