<?php

declare(strict_types=1);

namespace Inspector;

interface AdapterInterface
{
    /** @return array<string, string> */
    public function getAliases(): array;

    /** @return array<string, array{concrete: mixed, shared: bool}> */
    public function getBindings(): array;

    /** @return array<string> */
    public function getDependencies(string $service): array;

    /** @return array<string> */
    public function getBindingHistory(string $service): array;

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

    /**
     * @param string $service
     * @return array{
     *   type: string,
     *   message: string,
     *   code: int,
     *   file: string,
     *   line: int,
     *   exception: \Throwable
     * }|null
     */
    public function getResolutionError(string $service): ?array;
}
