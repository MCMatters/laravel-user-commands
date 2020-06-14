<?php

declare(strict_types=1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

use function class_exists;

use const null;

/**
 * Class AssignRole
 *
 * @package McMatters\UserCommands\Console\Commands
 */
class AssignRole extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'user:assign-role {user} {role}';

    /**
     * @var string
     */
    protected $description = 'Assign role for given user';

    /**
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function handle()
    {
        $config = $this->getConfig();

        $user = $this->getUser($config);
        $role = $this->getRole($config);

        $this->attachRole($user, $role, $config);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param \Illuminate\Database\Eloquent\Model $role
     * @param array $config
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function attachRole(Model $user, Model $role, array $config)
    {
        $roleMethod = Arr::get($config, 'update_password.attach_method');

        try {
            $reflection = new ReflectionClass(new $user());

            if (null === $roleMethod || !$reflection->hasMethod($roleMethod)) {
                $relation = null;

                if ($reflection->hasMethod('roles')) {
                    $relation = 'roles';
                } elseif ($reflection->hasMethod('role')) {
                    $relation = 'role';
                }

                if (null === $relation) {
                    throw new RuntimeException(
                        'Your model does not contain "role" relation or can not call "attach_method"'
                    );
                }

                $user->{$relation}()->attach($role->getKey());
            } else {
                $user->{$roleMethod}($role);
            }

            $this->info('Role successfully attached');
        } catch (ReflectionException $e) {
            $this->error('Something went wrong');
        }
    }

    /**
     * @param array $config
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \RuntimeException
     */
    protected function getRole(array $config): Model
    {
        $this->checkRoleModel($config);

        $identity = $this->argument('role');
        $roleModel = Arr::get($config, 'models.role');
        $identifiers = Arr::get(
            $config,
            'update_password.role_identifiers',
            ['id']
        );

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $roleModel::query();

        foreach ((array) $identifiers as $identifier) {
            $query->orWhere($identifier, $identity);
        }

        return $query->firstOrFail();
    }

    /**
     * @param array $config
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function checkRoleModel(array $config)
    {
        if (
            empty($config['models']['role']) ||
            !class_exists($config['models']['role'])
        ) {
            throw new RuntimeException('Please provide the "Role" model');
        }
    }
}
