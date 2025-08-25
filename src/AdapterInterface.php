<?php

declare(strict_types=1);

namespace Inspector;

interface AdapterInterface
{
    /** @return array<string> */
    public function getServices(): array;

    /** @return array<string, string> */
    public function getAliases(): array;

    /** @return array<string, array{concrete: mixed, shared: bool}> */
    public function getBindings(): array;

    /** @return array<string> */
    public function getDependencies(string $service): array;

    /** @return array<string> */
    public function getBindingHistory(string $service): array;

    /** @return mixed */
    public function resolve(string $service): mixed;
}
