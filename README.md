# Service Container Inspector

[![Coverage Status](https://codecov.io/gh/papoon/inspector/branch/main/graph/badge.svg)](https://codecov.io/gh/papoon/inspector)

A powerful tool for inspecting, visualizing, and tracking mutations in PHP service containers (Laravel, Symfony, PSR-11).

---

## Features

- Web dashboard for service inspection and visualization
- CLI commands for analysis and export
- Mutation tracking (bindings, aliases, etc.)
- Dependency graph export (Graphviz/D3.js)
- Service comparison across environments
- Tagged service listing
- Circular dependency and duplicate binding detection

---

## Installation

```bash
composer require papoon/inspector --dev
```

---

## Quick Start

### Web Dashboard

```bash
php -S localhost:8000 -t public
```
Visit [http://localhost:8000](http://localhost:8000) in your browser.

### CLI Usage

List services:
```bash
php bin/inspect inspector:list-services
```

Compare containers:
```bash
php bin/inspect inspector:compare-containers local staging prod
```

Export service map:
```bash
php bin/inspect inspector:export-map json
```

---

## Usage in Laravel

1. **Wrap your container with the Inspector adapter:**

    ```php
    use Inspector\Adapters\LaravelAdapter;
    use Illuminate\Container\Container;

    $container = app();
    $adapter = new LaravelAdapter($container);
    ```

2. **Track mutations:**

    ```php
    use Inspector\MutationEventDispatcher;

    $dispatcher = new MutationEventDispatcher();
    $dispatcher->listen(function ($mutation) {
        logger()->info('Container mutation', $mutation);
    });
    $adapter->setMutationDispatcher($dispatcher);
    ```

3. **Use adapter methods for mutations:**

    ```php
    $adapter->bind('foo', function () { return new Foo(); });
    $adapter->alias('foo', 'bar');
    ```

4. **Inspect services and mutations:**

    ```php
    use Inspector\Inspector;

    $inspector = new Inspector($adapter);
    $services = $inspector->browseServices();
    $mutations = $inspector->getMutations();
    ```

---

## Usage in Symfony

1. **Wrap your container with the Inspector adapter:**

    ```php
    use Inspector\Adapters\SymfonyAdapter;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = $this->container;
    $adapter = new SymfonyAdapter($container);
    ```

2. **Track mutations:**

    ```php
    use Inspector\MutationEventDispatcher;

    $dispatcher = new MutationEventDispatcher();
    $dispatcher->listen(function ($mutation) {
        // Log or display mutation events
    });
    $adapter->setMutationDispatcher($dispatcher);
    ```

3. **Use adapter methods for mutations:**

    ```php
    $adapter->setDefinition('foo', new Definition(Foo::class));
    $adapter->removeDefinition('foo');
    ```

4. **Inspect services and mutations:**

    ```php
    use Inspector\Inspector;

    $inspector = new Inspector($adapter);
    $services = $inspector->browseServices();
    $mutations = $inspector->getMutations();
    ```

---

## Test Coverage

To generate a coverage report locally:

```bash
vendor/bin/phpunit --coverage-html coverage
```
Open `coverage/index.html` in your browser to view the report.

---

## Contributing

Pull requests and issues are welcome!  
Please ensure all code is covered by tests and passes CI.

---

## License

MIT