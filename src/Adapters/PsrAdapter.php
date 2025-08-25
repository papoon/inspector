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
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getBindings(): array
    {
        return [];
    }

    public function getDependencies(string $service): array
    {
        return [];
    }

    public function getBindingHistory(string $service): array
    {
        return [];
    }

    public function resolve(string $service): mixed
    {
        return null;
    }
}
