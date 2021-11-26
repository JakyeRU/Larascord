<?php

namespace Jakyeru\Larascord\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larascord:install
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use this command to install Larascord.';

    private string|null $clientId;
    private string|null $clientSecret;
    private string|null $redirectUri;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Getting the user's input
        $this->clientId = $this->ask('What is your Discord application\'s client id?');
        $this->clientSecret = $this->ask('What is your Discord application\'s client secret?');
        $this->redirectUri = $this->ask('What is your Discord application\'s redirect uri?', 'http://localhost:8000/larascord/callback');

        // Validating the user's input
        try {$this->validateInput();} catch (\Exception $e) {$this->error($e->getMessage()); return;}

        // Installing laravel/breeze
        $this->requireComposerPackages('laravel/breeze^1.4');
        shell_exec('php artisan breeze:install');

        // Appending the secrets to the .env file
        $this->appendToEnvFile();

        // Creating the user migration files
        $this->createUserMigrationFiles();

        // Create the model files
        $this->createModelFiles();

        // Create the controller file
        $this->createControllerFiles();

        // Create the route files
        $this->createRouteFiles();

        // Create the view files
        $this->createViewFiles();

        // Asking the user to migrate the database
        if ($this->confirm('Do you want to run the migrations?', true)) {
            try {
                $this->call('migrate:fresh');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                $this->comment('You can run the migrations later by running the command:');
                $this->comment('php artisan migrate');
            }
        } else {
            $this->comment('You can run the migrations later by running the command:');
            $this->comment('php artisan migrate');
        }

        $this->info('Larascord has been successfully installed!');
    }

    /**
     * Validate the user's input.
     * @throws \Exception
     */
    protected function validateInput()
    {
        $rules = [
            'clientId' => ['required', 'numeric'],
            'clientSecret' => ['required', 'string'],
            'redirectUri' => ['required', 'url'],
        ];

        $validator = Validator::make([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri,
        ], $rules);

        $validator->validate();
    }

    /**
     * Append the secrets to the .env file.
     *
     * @return void
     */
    protected function appendToEnvFile()
    {

        (new Filesystem())->append('.env',PHP_EOL);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','DISCORD_CLIENT_ID='.$this->clientId);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','DISCORD_CLIENT_SECRET='.$this->clientSecret);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','DISCORD_GRANT_TYPE=authorization_code');

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','DISCORD_REDIRECT_URI='.$this->redirectUri);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','DISCORD_SCOPE=identify&email');
    }

    /**
     * Create the user migration files.
     *
     * @return void
     */
    public function createUserMigrationFiles()
    {
        (new Filesystem())->ensureDirectoryExists(database_path('migrations'));
        (new Filesystem())->copy(__DIR__ . '/../../database/migrations/2014_10_12_000000_create_users_table.php', database_path('migrations/2014_10_12_000000_create_users_table.php'));
    }

    /**
     * Create the user model files.
     *
     * @return void
     */
    public function createModelFiles()
    {
        (new Filesystem())->ensureDirectoryExists(app_path('Models'));
        (new Filesystem())->copy(__DIR__ . '/../../Models/User.php', app_path('Models/User.php'));
    }

    /**
     * Create the controller files.
     *
     * @return void
     */
    public function createControllerFiles()
    {
        (new Filesystem())->ensureDirectoryExists(app_path('Http/Controllers'));
        (new Filesystem())->copy(__DIR__ . '/../../Http/Controllers/DiscordController.php', app_path('Http/Controllers/DiscordController.php'));
    }

    /**
     * Create the route files.
     *
     * @return void
     */
    public function createRouteFiles() {
        (new Filesystem())->ensureDirectoryExists(base_path('routes'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../routes', base_path('routes'));
    }

    /**
     * Create the view files.
     *
     * @return void
     */
    public function createViewFiles()
    {
        (new Filesystem())->ensureDirectoryExists(resource_path('views'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../resources/views', resource_path('views'));
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }
}