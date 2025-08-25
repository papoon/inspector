<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;
use Illuminate\Container\Container;

class LaravelAdapter implements AdapterInterface
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /** @return array<string> */
    public function getServices(): array
    {
        // Get all bindings from the container
        return array_keys($this->container->getBindings());
    }

    /** @return array<string, string> */
    public function getAliases(): array
    {
        $reflection = new \ReflectionClass($this->container);
        if ($reflection->hasProperty('aliases')) {
            $property = $reflection->getProperty('aliases');
            $property->setAccessible(true);
            /** @var array<string, string> $aliases */
            $aliases = $property->getValue($this->container);
            return $aliases;
        }
        return [];
    }

    /** @return array<string, string> */
    public function getBindings(): array
    {
        return $this->container->getBindings();
    }

    /** @return array<string> */
    public function getDependencies(string $service): array
    {
        // Laravel does not expose dependencies directly; return empty for now
        return [];
    }

    /** @return array<string> */
    public function getBindingHistory(string $service): array
    {
        // Not tracked by default; return empty for now
        return [];
    }

    public function resolve(string $service): mixed
    {
        return $this->container->bound($service)
            ? $this->container->make($service)
            : null;
    }
}
