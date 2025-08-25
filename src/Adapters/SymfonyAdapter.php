<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;

class SymfonyAdapter implements AdapterInterface
{
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
