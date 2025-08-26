<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Throwable;

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
        if (!$this->container->has($service)) {
            return null;
        }
        return $this->container->get($service);
    }

    /** @return bool */
    public function isAutowired(string $service): bool
    {
        if (!$this->container->hasDefinition($service)) {
            return false;
        }
        return $this->container->getDefinition($service)->isAutowired();
    }

    public function inspectService(string $service): array
    {
        $class = null;
        $constructorDependencies = [];
        $isShared = false;

        if ($this->container->hasDefinition($service)) {
            $definition = $this->container->getDefinition($service);
            $class = $definition->getClass();
            $isShared = $definition->isShared();

            if ($class && class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $constructor = $reflection->getConstructor();
                if ($constructor) {
                    foreach ($constructor->getParameters() as $param) {
                        $type = $param->getType();
                        $typeName = null;
                        if ($type instanceof ReflectionNamedType) {
                            $typeName = $type->getName();
                        } elseif ($type instanceof ReflectionUnionType) {
                            $typeName = implode('|', array_map(
                                fn ($t) => $t->getName(),
                                $type->getTypes()
                            ));
                        }
                        $constructorDependencies[] = [
                            'name' => $param->getName(),
                            'type' => $typeName,
                            'isOptional' => $param->isOptional(),
                        ];
                    }
                }
            }
        }

        return [
            'class' => $class ?? null,
            'interfaces' => $class && class_exists($class) ? array_values(class_implements($class)) : [],
            'constructor_dependencies' => $constructorDependencies,
            'dependencies' => $this->getDependencies($service),
            'bindingHistory' => $this->getBindingHistory($service),
            'resolved' => $this->resolve($service),
            'shared' => $isShared,
        ];
    }

    /**
     * @return array{
     *   type: string,
     *   message: string,
     *   code: int,
     *   file: string,
     *   line: int,
     *   exception: Throwable
     * }|null
     */
    public function getResolutionError(string $service): ?array
    {
        try {
            $this->resolve($service);
            return null;
        } catch (Throwable $e) {
            return [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
            ];
        }
    }

    public function findDuplicateBindings(): array
    {
        $definitions = $this->container->getDefinitions();
        $counts = [];
        $duplicates = [];
        foreach (array_keys($definitions) as $service) {
            $counts[$service] = ($counts[$service] ?? 0) + 1;
        }
        foreach ($counts as $service => $count) {
            if ($count > 1) {
                $duplicates[] = $service;
            }
        }
        return $duplicates;
    }

    public function findAliasLoops(): array
    {
        $aliases = $this->getAliases();
        $loops = [];
        foreach ($aliases as $alias => $target) {
            $visited = [$alias];
            $current = $target;
            while (isset($aliases[$current])) {
                if (in_array($current, $visited, true)) {
                    $loops[] = array_merge($visited, [$current]);
                    break;
                }
                $visited[] = $current;
                $current = $aliases[$current];
            }
        }
        return $loops;
    }
}
