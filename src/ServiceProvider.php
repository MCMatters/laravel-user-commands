<?php

declare(strict_types = 1);

namespace McMatters\DbCommands;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use McMatters\UserCommands\Console\Commands\{
    AssignRole, Sanitize, UpdatePassword
};

/**
 * Class ServiceProvider
 *
 * @package McMatters\UserCommands
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/user-commands.php';

        $this->publishes([
            $configPath => $this->configPath('user-commands.php'),
        ], 'config');

        $this->mergeConfigFrom($configPath, 'user-commands');
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.user.assign-role', function () {
            return new AssignRole();
        });
        $this->app->singleton('command.user.sanitize', function () {
            return new Sanitize();
        });
        $this->app->singleton('command.user.update-password', function () {
            return new UpdatePassword();
        });

        $this->commands([
            'command.user.assign-role',
            'command.user.sanitize',
            'command.user.update-password',
        ]);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function configPath(string $file): string
    {
        if (function_exists('config_path')) {
            return config_path($file);
        }

        return base_path("config/{$file}");
    }
}
