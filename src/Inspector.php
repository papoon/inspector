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
     *     dependencies: array<string>,
     *     bindingHistory: array<string>,
     *     resolved: mixed
     * }
     */
    public function inspectService(string $service): array
    {
        return [
            'dependencies' => $this->adapter->getDependencies($service),
            'bindingHistory' => $this->adapter->getBindingHistory($service),
            'resolved' => $this->adapter->resolve($service),
        ];
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
