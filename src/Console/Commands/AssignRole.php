<?php

declare(strict_types = 1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

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
    protected $description = 'Update role for given user';

    /**
     * @return void
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function handle()
    {
        $config = $this->getConfig();

        $user = $this->getUser($config);
        $role = $this->getRole($config);

        $this->attachRole($user, $role, $config);
    }

    /**
     * @param Model $user
     * @param Model $role
     * @param array $config
     *
     * @throws RuntimeException
     */
    protected function attachRole(Model $user, Model $role, array $config)
    {
        $roleMethod = array_get($config, 'update_password.attach_method');

        try {
            $reflection = new ReflectionClass(new $user);

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
     * @return Model
     * @throws RuntimeException
     */
    protected function getRole(array $config): Model
    {
        $this->checkRoleModel($config);

        $identity = $this->argument('role');
        $roleModel = array_get($config, 'models.role');
        $identifiers = (array) array_get(
            $config,
            'update_password.role_identifiers',
            ['id']
        );

        $query = $roleModel::query();

        foreach ($identifiers as $identifier) {
            $query->orWhere($identifier, '=', $identity);
        }

        return $query->firstOrFail();
    }

    /**
     * @param array $config
     *
     * @throws RuntimeException
     */
    protected function checkRoleModel(array $config)
    {
        if (empty($config['models']['role']) ||
            !class_exists($config['models']['role'])
        ) {
            throw new RuntimeException('Please provide the "Role" model.');
        }
    }
}
