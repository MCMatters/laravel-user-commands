<?php

declare(strict_types = 1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Support\Collection;
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

        $users->each(function ($user) use ($emailColumn, $domain) {
            $email = $user->getAttribute($emailColumn);
            $newEmail = preg_replace('/@(.*)$/', "@{$domain}-$1", $email);

            $user->setAttribute($emailColumn, $newEmail);
            $user->save();
        });
    }

    /**
     * @param array $config
     *
     * @return Collection
     */
    protected function getUsers(array $config): Collection
    {
        $userModel = array_get($config, 'models.user');

        $query = $userModel::query();

        foreach ((array) array_get($config, 'sanitize.scopes', []) as $scope) {
            if (is_array($scope)) {
                $scopeName = array_shift($scope);

                $query->{$scopeName}(...$scope);
            } else {
                $query->{$scope()};
            }
        }

        return $query->get();
    }
}
