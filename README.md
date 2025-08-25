
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
```

#### In a Laravel Project

1. **Register the commands in `app/Console/Kernel.php`:**

```php
protected $commands = [
    \Inspector\Console\ListServicesCommand::class,
    \Inspector\Console\InspectServiceCommand::class,
];
```

2. **Run with Artisan:**

```bash
php artisan services:list
php artisan services:inspect <service>
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

Feel free to open issues or submit