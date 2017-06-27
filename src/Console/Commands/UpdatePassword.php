<?php

declare(strict_types = 1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class UpdatePassword
 *
 * @package McMatters\UserCommands
 */
class UpdatePassword extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'user:update-password {user}';

    /**
     * @var string
     */
    protected $description = 'Update user\'s password';

    /**
     * @return void
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function handle()
    {
        $password = $this->secret('Please write new password');

        $config = $this->getConfig();
        $user = $this->getUser($config);

        $passwordColumn = array_get($config, 'columns.password', 'password');
        $password = array_get($config, 'password.need_hash', false)
            ? $this->crypt($password)
            : $password;

        $user->update([$passwordColumn => $password]);

        $this->info('Password successfully updated.');
    }

    /**
     * @param string $password
     *
     * @return string
     */
    protected function crypt(string $password): string
    {
        if (function_exists('bcrypt')) {
            return bcrypt($password);
        }

        return app('hash')->make($password, []);
    }
}
