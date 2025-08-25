<?php

declare(strict_types=1);

namespace Inspector;

interface AdapterInterface
{
    public function getServices(): array;

    public function getAliases(): array;

    public function getBindings(): array;

    public function getDependencies(string $service): array;

    public function getBindingHistory(string $service): array;

    public function resolve(string $service): mixed;
}
