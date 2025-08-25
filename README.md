# Service Container Inspector

Framework-agnostic tool to inspect and debug PHP service containers.

## Installation

```bash
composer install
```

## Usage

### Web Dashboard

Start the built-in PHP server:

```bash
php -S 0.0.0.0:8080 -t public
```

Visit [http://localhost:8080](http://localhost:8080) to browse and inspect services.

---

### CLI Usage

#### In a Vanilla PHP Project

Run the CLI tool directly:

```bash
php bin/inspect services:list
php bin/inspect services:inspect <service>
php bin/inspect services:list --filter=foo           # Filter/search services
php bin/inspect services:list --format=json          # Output as JSON for CI
php bin/inspect services:tags                        # List service tags (Laravel/Symfony)
php bin/inspect services:contextual                  # List contextual bindings (Laravel)
php bin/inspect services:parameters                  # List parameters (Symfony)
php bin/inspect services:autowired <service>         # Check if service is autowired (Symfony)
php bin/inspect services:graph                       # Show dependency graph
php bin/inspect services:circular                    # Detect circular dependencies
php bin/inspect services:trace <service>             # Trace service resolution
```

To use the Symfony adapter, pass `symfony` as the first argument:

```bash
php bin/inspect symfony services:list
php bin/inspect symfony services:tags
php bin/inspect symfony services:parameters
php bin/inspect symfony services:autowired <service>
```

#### In a Laravel Project

1. **Register the commands in `app/Console/Kernel.php`:**

```php
protected $commands = [
    \Inspector\Console\ListServicesCommand::class,
    \Inspector\Console\InspectServiceCommand::class,
    \Inspector\Console\ListTagsCommand::class,
    \Inspector\Console\ListContextualBindingsCommand::class,
    \Inspector\Console\DependencyGraphCommand::class,
    \Inspector\Console\CircularDependencyCommand::class,
    \Inspector\Console\ServiceTraceCommand::class,
];
```

2. **Run with Artisan:**

```bash
php artisan services:list
php artisan services:inspect <service>
php artisan services:tags
php artisan services:contextual
php artisan services:graph
php artisan services:circular
php artisan services:trace <service>
```

---

## Features

- **List all registered services**
- **Inspect individual service details**
- **Filter/search services by name**
- **Output results in JSON format for CI integration**
- **List service tags (Laravel/Symfony)**
- **List contextual bindings (Laravel)**
- **List container parameters (Symfony)**
- **Check autowiring status (Symfony)**
- **Show service dependency graph**
- **Detect circular dependencies**
- **Trace service resolution**
- **Works with Laravel, Symfony, and any PSR-11 compatible container**

---

## Container Configuration

This package supports Laravel, Symfony, and any PSR-11 compatible container.

### Laravel

No configuration needed if used inside a Laravel app. The inspector will use the real Laravel container via `app()`.

### Symfony

No configuration needed if used inside a Symfony app. The inspector will use the real Symfony container via `$GLOBALS['kernel']->getContainer()`.

### PSR-11

To use a PSR-11 compatible container, create a config file at `config/container.php`:

```php
<?php

return [
    'psr' => [
        'class' => \Your\Psr\Container::class, // Fully qualified class name
        'args' => [
            // Constructor arguments for your container, if any
        ],
    ],
];
```

Then select "PSR-11" in the dashboard or pass `adapter=psr` as a query parameter:

```
http://localhost:8080/?adapter=psr
```

If your container requires additional setup, do it in the config file or modify `public/index.php` as needed.

---

### Custom Container Configuration

You can place your `config/container.php` in your project root or use the default provided by this package.  
The inspector will automatically use your custom config if present.

Example location:
```
your-project/config/container.php
```

---

## Testing

Run PHPUnit tests:

```bash
vendor/bin/phpunit
```

---

## Code Style & Static Analysis

Format code with PHP CS Fixer:

```bash
vendor/bin/php-cs-fixer fix
```

Run static analysis with PHPStan:

```bash
vendor/bin/phpstan analyse
```

---

## Contributing

Feel free to open issues or submit pull requests!