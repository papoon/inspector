<?php

namespace Inspector;

use Throwable;

class Inspector
{
    protected AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return array<string>
     */
    public function browseServices(): array
    {
        return $this->adapter->getServices();
    }

    /**
     * @return array{
     *   class?: string|null,
     *   interfaces?: array<string>,
     *   constructor_dependencies?: array<array{name: string, type: string|null, isOptional: bool}>,
     *   dependencies: array<string>,
     *   bindingHistory: array<string>,
     *   resolved: mixed,
     *   shared: bool|null
     * }
     */
    public function inspectService(string $service): array
    {
        $adapterDetails = $this->adapter->inspectService($service);

        // Always provide all keys, even if missing from adapter
        return array_merge([
            'class' => null,
            'interfaces' => [],
            'constructor_dependencies' => [],
            'dependencies' => [],
            'bindingHistory' => [],
            'resolved' => null,
            'shared' => null,
        ], $adapterDetails);
    }

    /**
     * @return array<string> List of service names that cannot be resolved
     */
    public function findUnresolvableServices(): array
    {
        $broken = [];
        foreach ($this->browseServices() as $service) {
            try {
                $this->adapter->resolve($service);
            } catch (Throwable $e) {
                $broken[] = $service;
            }
        }
        return $broken;
    }

    /**
     * @return array<string, array{
     *   type: string,
     *   message: string,
     *   code: int,
     *   file: string,
     *   line: int,
     *   exception: Throwable
     * }>
     */
    public function findUnresolvableServicesWithDetails(): array
    {
        $broken = [];
        foreach ($this->browseServices() as $service) {
            $error = $this->adapter->getResolutionError($service);

            if ($error) {
                $broken[$service] = $error;
            }
        }
        return $broken;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @return array<string> List of duplicate service names
     */
    public function getDuplicateBindings(): array
    {
        return $this->adapter->findDuplicateBindings();
    }

    /**
     * @return array<int, array<string>> List of alias loops (each loop is an array of service names)
     */
    public function getAliasLoops(): array
    {
        return $this->adapter->findAliasLoops();
    }
}
