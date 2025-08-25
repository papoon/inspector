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
