<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;
use Psr\Container\ContainerInterface;

class PsrAdapter implements AdapterInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getServices(): array
    {
        // Try common methods for service discovery
        if (method_exists($this->container, 'getServiceIds')) {
            return $this->container->getServiceIds();
        }
        if (method_exists($this->container, 'keys')) {
            return $this->container->keys();
        }
        // If not supported, return empty
        return [];
    }

    public function getAliases(): array
    {
        // No standard way in PSR-11
        return [];
    }

    public function getBindings(): array
    {
        // No standard way in PSR-11
        return [];
    }

    public function getDependencies(string $service): array
    {
        // No standard way in PSR-11
        return [];
    }

    public function getBindingHistory(string $service): array
    {
        // No standard way in PSR-11
        return [];
    }

    public function resolve(string $service): mixed
    {
        if ($this->container->has($service)) {
            return $this->container->get($service);
        }
        return null;
    }

    public function getContainerType(): string
    {
        return get_class($this->container);
    }
}
