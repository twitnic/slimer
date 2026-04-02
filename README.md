# twitnic/slimer

`twitnic/slimer` is a standalone Composer package that gives Slim Framework 2 projects an Artisan-style command line experience.

It ships with:

- a `vendor/bin/slimer` console entrypoint
- Slim 2 bootstrap discovery via `.slimer.php`
- `route:list` for inspecting registered routes
- `serve` for running the built-in PHP web server
- `init`, `make:command`, `make:controller`, `make:middleware`, and `make:view`
- hooks for custom project commands

## Installation

```bash
composer require twitnic/slimer
```

## Quick start

Initialize the package inside an existing Slim 2 application:

```bash
vendor/bin/slimer init
```

That creates a `.slimer.php` file. Point the generated bootstrap callback to your Slim entry script if needed, then inspect the available commands:

```bash
vendor/bin/slimer list
vendor/bin/slimer about
vendor/bin/slimer route:list
```

## Configuration

Slimer looks for configuration in this order:

1. the path from `SLIMER_CONFIG`
2. `.slimer.php`
3. `config/slimer.php`
4. `app/config/slimer.php`

Example configuration:

```php
<?php

return array(
    'bootstrap' => 'app/bootstrap.php',

    'commands' => array(
        App\Console\Commands\CleanupCommand::class,
    ),

    'generators' => array(
        'commands_path' => 'app/Console/Commands',
        'command_namespace' => 'App\\Console\\Commands',
        'controllers_path' => 'app/controllers',
        'controller_namespace' => 'App\\Controllers',
        'middleware_path' => 'app/middleware',
        'middleware_namespace' => 'App\\Middleware',
        'views_path' => 'app/views',
    ),
);
```

## Built-in commands

```bash
vendor/bin/slimer about
vendor/bin/slimer init
vendor/bin/slimer route:list
vendor/bin/slimer serve --host=127.0.0.1 --port=8080
vendor/bin/slimer make:command Cleanup
vendor/bin/slimer make:controller Admin/User
vendor/bin/slimer make:middleware ApiAuth
vendor/bin/slimer make:view admin/dashboard
```

## Notes

- `route:list` expects a bootstrapped `Slim\Slim` instance.
- Prefer a dedicated bootstrap file that builds the application without immediately calling `$app->run()`.
- Custom commands can either be instantiated directly, returned from a callable, or referenced by class name.
- Generated middleware classes extend `\Slim\Middleware`, which matches Slim 2.
