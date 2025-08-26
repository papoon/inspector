<?php

namespace Inspector;

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
     *   resolved: mixed
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
        ], $adapterDetails);
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
