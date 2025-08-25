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

    /** @return array<string, array{concrete: mixed, shared: bool}> */
    public function getBindings(): array
    {
        $bindings = [];
        foreach ($this->container->getDefinitions() as $id => $definition) {
            $bindings[$id] = [
                'concrete' => $definition->getClass(), // Use class as "concrete"
                'shared' => $definition->isShared(),   // Use isShared for "shared"
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

    /** @return array<string, array<string>> Tag => [services] */
    public function getTags(): array
    {
        $tags = [];
        foreach ($this->container->getDefinitions() as $id => $definition) {
            foreach ($definition->getTags() as $tag => $attributes) {
                if (!isset($tags[$tag])) {
                    $tags[$tag] = [];
                }
                $tags[$tag][] = $id;
            }
        }
        return $tags;
    }

    /** @return array<string, mixed> */
    public function getParameters(): array
    {
        return $this->container->getParameterBag()->all();
    }

    public function resolve(string $service): mixed
    {
        if ($this->container->has($service)) {
            return $this->container->get($service);
        }
        return null;
    }

    /** @return bool */
    public function isAutowired(string $service): bool
    {
        if (!$this->container->hasDefinition($service)) {
            return false;
        }
        return $this->container->getDefinition($service)->isAutowired();
    }
}
