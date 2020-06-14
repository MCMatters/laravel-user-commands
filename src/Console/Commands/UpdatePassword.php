<?php

declare(strict_types=1);

namespace McMatters\UserCommands\Console\Commands;

use Illuminate\Support\Arr;

use const false;

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
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function handle()
    {
        $password = $this->secret('Please write new password');

        $config = $this->getConfig();
        $user = $this->getUser($config);

        $passwordColumn = Arr::get($config, 'columns.password', 'password');
        $password = Arr::get($config, 'password.need_hash', false)
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
        return $this->getLaravel()->make('hash')->make($password, []);
    }
}
