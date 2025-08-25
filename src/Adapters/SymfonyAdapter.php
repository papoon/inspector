<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SymfonyAdapter implements AdapterInterface
{
    protected ContainerBuilder $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /** @return array<string> */
    public function getServices(): array
    {
        return array_keys($this->container->getDefinitions());
    }

    /** @return array<string, string> */
    public function getAliases(): array
    {
        $aliases = [];
        foreach ($this->container->getAliases() as $alias => $serviceId) {
            $aliases[(string)$alias] = (string)$serviceId;
        }
        return $aliases;
    }

    /** @return array<string, array{class: string|null, public: bool}> */
    public function getBindings(): array
    {
        $bindings = [];
        foreach ($this->container->getDefinitions() as $id => $definition) {
            $bindings[$id] = [
                'class' => $definition->getClass(),
                'public' => $definition->isPublic(),
            ];
        }
        return $bindings;
    }

    /** @return array<string> */
    public function getDependencies(string $service): array
    {
        if (!$this->container->hasDefinition($service)) {
            return [];
        }
        $definition = $this->container->getDefinition($service);
        $arguments = $definition->getArguments();
        $deps = [];
        foreach ($arguments as $arg) {
            if (is_string($arg)) {
                $deps[] = $arg;
            }
        }
        return $deps;
    }

    /** @return array<string> */
    public function getBindingHistory(string $service): array
    {
        // Symfony does not track binding history by default
        return [];
    }

    public function resolve(string $service): mixed
    {
        if ($this->container->has($service)) {
            return $this->container->get($service);
        }
        return null;
    }
}
