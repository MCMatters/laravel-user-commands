<?php

declare(strict_types = 1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Class Sanitize
 *
 * @package McMatters\UserCommands\Console\Commands
 */
class Sanitize extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'user:sanitize { --domain= }';

    /**
     * @var string
     */
    protected $description = 'Sanitize emails of users';

    /**
     * @return void
     * @throws RuntimeException
     */
    public function handle()
    {
        $domain = $this->option('domain') ?: 'localhost';

        $config = $this->getConfig();
        $emailColumn = $this->getEmailColumn($config);
        $users = $this->getUsers($config);

        $sanitized = 0;

        $users->each(function (Model $user) use ($emailColumn, $domain, &$sanitized) {
            $email = $user->getAttribute($emailColumn);
            $newEmail = preg_replace('/@(.*)$/', "@{$domain}-$1", $email);

            $user->update([$emailColumn => $newEmail]);

            $sanitized++;
        });

        $this->info("Successfully sanitized {$sanitized} emails");
    }

    /**
     * @param array $config
     *
     * @return Builder
     */
    protected function getUsers(array $config): Builder
    {
        $userModel = array_get($config, 'models.user');

        $query = $userModel::query();

        foreach ((array) array_get($config, 'sanitize.scopes', []) as $scope) {
            if (is_array($scope)) {
                $scopeName = array_shift($scope);

                $query->{$scopeName}(...$scope);
            } else {
                $query->{$scope}();
            }
        }

        return $query;
    }
}
