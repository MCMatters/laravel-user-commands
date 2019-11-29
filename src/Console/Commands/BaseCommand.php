<?php

declare(strict_types = 1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use RuntimeException;

use function class_exists;

use const null;

/**
 * Class BaseCommand
 *
 * @package McMatters\UserCommands\Console\Commands
 */
abstract class BaseCommand extends Command
{
    /**
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function getConfig(): array
    {
        $config = Config::get('user-commands');

        $this->checkUserModel($config);

        return $config;
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getUserModel(array $config): string
    {
        return Arr::get($config, 'models.user');
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getIdColumn(array $config): string
    {
        return Arr::get($config, 'columns.id', 'id');
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getEmailColumn(array $config): string
    {
        return Arr::get($config, 'columns.email', 'email');
    }

    /**
     * @param array $config
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function getUser(array $config): Model
    {
        $identity = $this->argument('user');

        $userModel = $this->getUserModel($config);
        $emailColumn = $this->getEmailColumn($config);
        $nameColumn = Arr::get($config, 'columns.name');

        return $userModel::query()
            ->where(Arr::get($config, 'columns.id', 'id'), $identity)
            ->when(null !== $emailColumn, function ($q) use ($identity, $emailColumn) {
                $q->orWhere($emailColumn, $identity);
            })
            ->when(null !== $nameColumn, function ($q) use ($identity, $nameColumn) {
                $q->orWhere($nameColumn, $identity);
            })
            ->firstOrFail();
    }

    /**
     * @param array $config
     *
     * @throws \RuntimeException
     */
    protected function checkUserModel(array $config)
    {
        if (empty($config['models']['user']) ||
            !class_exists($config['models']['user'])
        ) {
            throw new RuntimeException('Please provide the "User" model.');
        }
    }
}
