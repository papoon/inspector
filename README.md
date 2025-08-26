# Service Container Inspector

Framework-agnostic tool to inspect and debug PHP service containers.

[![Coverage Status](https://codecov.io/gh/papoon/inspector/branch/main/graph/badge.svg)](https://codecov.io/gh/papoon/inspector)

## Installation

```bash
composer require papoon/inspector
```

## Usage

### Web Dashboard

#### How to Access in Laravel, Symfony, or Vanilla PHP

This package provides a web dashboard via `public/index.php` that works with Laravel, Symfony, or any PSR-11 compatible container.

**Steps:**

1. **Expose the dashboard:**
   - Copy or symlink `vendor/papoon/inspector/public/index.php` to your project's `public/inspector.php` (or any desired location).
   - Example for Laravel:
     ```bash
     cp vendor/papoon/inspector/public/index.php public/inspector.php
     ```

2. **Visit the dashboard in your browser:**
   ```
   http://localhost:8000/inspector.php
   ```

3. **Adapter selection:**
   - Use the dropdown on the dashboard to switch between Laravel, Symfony, or PSR-11 containers.
   - The dashboard auto-detects the real Laravel or Symfony container for full introspection.
   - For PSR-11, configure your container in `config/container.php` (see below).

4. **Search/filter services:**
   - Use the search box to filter services by name.

#### PSR-11 Container Configuration

If you want to use a PSR-11 compatible container, create or edit `config/container.php` in your project root:

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

The dashboard will automatically use your custom config if present.

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

- List all registered services
- Inspect individual service details
- Filter/search services by name
- Output results in JSON format for CI integration
- List service tags (Laravel/Symfony)
- List contextual bindings (Laravel)
- List container parameters (Symfony)
- Check autowiring status (Symfony)
- Show service dependency graph
- Detect circular dependencies
- Trace service resolution
- Works with Laravel, Symfony, and any PSR-11 compatible container

---

## Testing

```bash
vendor/bin/phpunit
```

---

## Code Style & Static Analysis

```bash
vendor/bin/php-cs-fixer fix
vendor/bin/phpstan analyse
```

---

## Contributing

Feel free to open issues or submit pull requests!