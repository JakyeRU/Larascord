<?php

namespace Jakyeru\Larascord\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Finder\Finder;
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

    /*
     * The Discord application's client id.
     *
     * @var string|null
     */
    private ?string $clientId;

    /*
     * The Discord application's client secret.
     *
     * @var string|null
     */
    private ?string $clientSecret;

    /*
     * The route prefix.
     *
     * @var string|null
     */
    private ?string $prefix;

    /*
     * The Discord bot's access token.
     *
     * @var string|null
     */
    private ?string $accessToken;

    /*
     * Whether dark mode should be enabled.
     *
     * @var bool|null
     */
    private ?bool $darkMode;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Getting the user's input
        $this->clientId = $this->ask('What is your Discord application\'s client id?');
        $this->clientSecret = $this->ask('What is your Discord application\'s client secret?');
        $this->prefix = $this->ask('What route prefix should Larascord use?', 'larascord');
        $this->darkMode = $this->confirm('Do you want to install laravel/breeze with dark mode?', true);

        if ($this->confirm('Do you want to provide an access token for your Discord bot now? (It is required to use the roles feature.)', false)) {
            $this->accessToken = $this->ask('What is your access token?');
        } else {
            $this->accessToken = null;
            $this->warn('You can provide an access token later by setting the LARASCORD_DISCORD_TOKEN variable in your .env file.');
        }

        // Validating the user's input
        try {$this->validateInput();} catch (\Exception $e) {$this->error($e->getMessage()); return;}

        // Installing laravel/breeze
        $this->info('Installing Larascord...');
        $this->requireComposerPackages('laravel/breeze:^1.19', '-q');
        if ($this->darkMode) {
            shell_exec('php artisan breeze:install blade --dark');
        } else {
            shell_exec('php artisan breeze:install blade');
        }

        // Appending the secrets to the .env file
        $this->appendToEnvFile();

        // Creating the user migration files
        $this->createUserMigrationFiles();

        // Create the model files
        $this->createModelFiles();

        // Create the view files
        $this->createViewFiles();

        // Create the event files
        $this->createEventFiles();

        // Remove Laravel Breeze routes
        $this->replaceBreezeRoutes();

        // Asking the user to build the assets
        if ($this->confirm('Do you want to build the assets?', true)) {
            try {
                shell_exec('npm install --silent');
                shell_exec('npm run build --silent');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                $this->comment('Please execute the "npm install && npm run build" command to build your assets.');
            }
        } else {
            $this->comment('Please execute the "npm install && npm run build" command to build your assets.');
        }

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

        // Automatically publishing the configuration file
        $this->call('larascord:publish');

        $this->alert('Please make sure you add "' . env('APP_URL', 'http://localhost:8000') . '/' . env('LARASCORD_PREFIX', 'larascord') . '/callback' . '" to your Discord application\'s redirect urls in the OAuth2 tab.');
        $this->warn('If the domain doesn\'t match your current environment\'s domain you need to set it manually in the .env file. (APP_URL)');

        $this->info('Larascord has been successfully installed!');
    }

    /**
     * Validate the user's input.
     *
     * @throws \Exception
     */
    protected function validateInput(): void
    {
        $rules = [
            'clientId' => ['required', 'numeric'],
            'clientSecret' => ['required', 'string'],
            'prefix' => ['required', 'string'],
        ];

        $validator = Validator::make([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'prefix' => $this->prefix,
        ], $rules);

        $validator->validate();

        if ($this->accessToken) {
            $client = new \GuzzleHttp\Client();

            try {
                $response = $client->get('https://discord.com/api/users/@me', [
                    'headers' => [
                        'authorization' => 'Bot ' . $this->accessToken
                    ],
                ]);

                $tokenData = json_decode($response->getBody()->getContents(), true);

                if (!$this->confirm('The provided access token belongs to the bot "' . $tokenData['username'] . '#' . $tokenData['discriminator'] . '" (ID: ' . $tokenData['id'] .'). Is this correct?', true)) {
                    $this->error('Installation aborted.');
                    exit(1);
                }
            } catch (\Exception $e) {
                if ($e->getCode() === 401) {
                    throw new \Exception('The provided access token is invalid.');
                } else {
                    throw new \Exception('The provided access token is invalid or the Discord API is currently unavailable.');
                }
            }
        }
    }

    /**
     * Append the secrets to the .env file.
     */
    protected function appendToEnvFile(): void
    {

        (new Filesystem())->append('.env',PHP_EOL);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','LARASCORD_CLIENT_ID='.$this->clientId);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','LARASCORD_CLIENT_SECRET='.$this->clientSecret);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','LARASCORD_GRANT_TYPE=authorization_code');

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','LARASCORD_PREFIX='.$this->prefix);

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','LARASCORD_SCOPE=identify&email');

        (new Filesystem())->append('.env',PHP_EOL);
        (new Filesystem())->append('.env','LARASCORD_DISCORD_TOKEN='.$this->accessToken);
    }

    /**
     * Create the user migration files.
     */
    public function createUserMigrationFiles(): void
    {
        (new Filesystem())->ensureDirectoryExists(database_path('migrations'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../database/migrations/', database_path('migrations/'));
    }

    /**
     * Create the user model files.
     */
    public function createModelFiles(): void
    {
        (new Filesystem())->ensureDirectoryExists(app_path('Models'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../Models/', app_path('Models/'));
    }

    /**
     * Create the view files.
     */
    public function createViewFiles(): void
    {
        (new Filesystem())->ensureDirectoryExists(resource_path('views'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../resources/views', resource_path('views'));

        if (!$this->darkMode) {
            $this->removeDarkClasses((new Finder())
                ->in(resource_path('views'))
                ->name('*.blade.php')
                ->notName('welcome.blade.php')
            );
        }
    }

    /**
     * Create the event files.
     */
    public function createEventFiles(): void
    {
        (new Filesystem())->ensureDirectoryExists(app_path('Events'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../Events/', app_path('Events/'));
    }

    /**
     * Removes Laravel Breeze's default routes and replaces them with Larascord's routes.
     */
    public function replaceBreezeRoutes(): void
    {
        (new Filesystem())->ensureDirectoryExists(resource_path('routes'));
        (new Filesystem())->copy(__DIR__ . '/../../routes/web.php', base_path('routes/web.php'));
        (new Filesystem())->delete(base_path('routes/auth.php'));
    }

    /**
     * Installs the given Composer Packages into the application.
     */
    protected function requireComposerPackages(mixed $packages): void
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
                if ($type === Process::ERR) {
                    $this->error(trim($output));
                    exit(1);
                } else {
                    $this->output->write($output);
                }
            });
    }

    /**
     * Remove Tailwind dark classes from the given files.
     */
    protected function removeDarkClasses(Finder $finder): void
    {
        foreach ($finder as $file) {
            file_put_contents($file->getPathname(), preg_replace('/\sdark:[^\s"\']+/', '', $file->getContents()));
        }
    }
}