<?php

declare(strict_types=1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

use function array_shift, is_array, preg_replace;

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
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        $domain = $this->option('domain') ?: 'localhost';

        $config = $this->getConfig();
        $emailColumn = $this->getEmailColumn($config);
        $users = $this->getUsers($config);

        $sanitized = 0;

        /** @var \Illuminate\Database\Eloquent\Model $user */
        foreach ($users as $user) {
            $email = $user->getAttribute($emailColumn);
            $newEmail = preg_replace('/@(.*)$/', "@{$domain}-$1", $email);

            $user->update([$emailColumn => $newEmail]);

            $sanitized++;
        }

        $this->info("Successfully sanitized {$sanitized} emails");
    }

    /**
     * @param array $config
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getUsers(array $config): Builder
    {
        $userModel = Arr::get($config, 'models.user');

        $query = $userModel::query();

        foreach ((array) Arr::get($config, 'sanitize.scopes', []) as $scope) {
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
