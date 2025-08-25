<?php
namespace Inspector;

class Inspector
{
    protected AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function browseServices(): array
    {
        return $this->adapter->getServices();
    }

    public function inspectService(string $service): array
    {
        return [
            'dependencies' => $this->adapter->getDependencies($service),
            'bindingHistory' => $this->adapter->getBindingHistory($service),
            'resolved' => $this->adapter->resolve($service),
        ];
    }
}