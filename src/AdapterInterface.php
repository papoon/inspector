<?php

declare(strict_types=1);

namespace Inspector;

interface AdapterInterface
{
    /** @return array<string> */
    public function getServices(): array;

    /**
     * @return array{
     *   class: string|null,
     *   interfaces: array<string>,
     *   constructor_dependencies: array<array{name: string, type: string|null, isOptional: bool}>,
     *   dependencies: array<string>,
     *   bindingHistory: array<string>,
     *   resolved: mixed,
     *   shared: bool|null
     * }
     */
    public function inspectService(string $service): array;

    /**
     * Attempt to resolve a service. Returns the instance or null if not resolvable.
     * @param string $service
     * @return mixed|null
     */
    public function resolve(string $service);
}
