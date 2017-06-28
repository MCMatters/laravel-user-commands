## Laravel User Commands

Package with laravel user commands.

### Installation

```bash
composer require mcmatters/laravel-user-commands
```

Include the service provider within your `config/app.php` file.

```php
'providers' => [
    McMatters\UserCommands\ServiceProvider::class,
]
```

Publish the configuration.

```bash
php artisan vendor:publish --provider="McMatters\UserCommands\ServiceProvider"
```

Then open the `config/user-commands.php` and configure namespaces for your models.

### Requirements

This package requires php `7.0` or higher and Laravel `5.2` or higher.

### Usage

Available commands:

* `php artisan user:assign-role {user} {role}` - Attach role for given user
* `php artisan user:sanitize` - Sanitize all user emails. You can configure which users should be taken through the scopes
* `php artisan user:update-password {user}` - Update password for given user
