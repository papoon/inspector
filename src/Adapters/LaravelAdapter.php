<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;
use Inspector\MutationEventDispatcher;
use Illuminate\Container\Container;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Throwable;

class LaravelAdapter implements AdapterInterface
{
    protected Container $container;
    protected array $mutations = [];
    protected ?MutationEventDispatcher $mutationDispatcher = null;

    public function __construct(Container $container)
    {
        $this->container = $container;

        // Optionally, wrap container methods to track mutations
        $container->afterResolving(function ($object, $app) {
            // Example: track resolution (optional)
            $this->mutations[] = [
                'timestamp' => microtime(true),
                'type' => 'resolve',
                'action' => 'resolved',
                'service' => is_object($object) ? get_class($object) : (string)$object,
                'details' => [],
            ];
        });
    }

    /** @return array<string> */
    public function getServices(): array
    {
        // Get all bindings from the container
        return array_keys($this->container->getBindings());
    }

    /** @return array<string, string> */
    public function getAliases(): array
    {
        $reflection = new ReflectionClass($this->container);
        if ($reflection->hasProperty('aliases')) {
            $property = $reflection->getProperty('aliases');
            $property->setAccessible(true);
            /** @var array<string, string> $aliases */
            $aliases = $property->getValue($this->container);
            return $aliases;
        }
        return [];
    }

    /** @return array<string, array{concrete: mixed, shared: bool}> */
    public function getBindings(): array
    {
        return $this->container->getBindings();
    }

    /** @return array<string> */
    public function getDependencies(string $service): array
    {
        // Laravel does not expose dependencies directly; return empty for now
        return [];
    }

    /** @return array<string> */
    public function getBindingHistory(string $service): array
    {
        // Not tracked by default; return empty for now
        return [];
    }

    /**
     * @return array<string, array<string>> Tag => [services]
     */
    public function getTags(): array
    {
        $reflection = new ReflectionClass($this->container);
        if ($reflection->hasProperty('tags')) {
            $property = $reflection->getProperty('tags');
            $property->setAccessible(true);
            /** @var array<string, array<string>> $tags */
            $tags = $property->getValue($this->container);
            return $tags;
        }
        return [];
    }

    /**
     * @return array<string, array<string, mixed>> Abstract => [Context => concrete]
     */
    public function getContextualBindings(): array
    {
        $reflection = new ReflectionClass($this->container);
        if ($reflection->hasProperty('contextual')) {
            $property = $reflection->getProperty('contextual');
            $property->setAccessible(true);
            /** @var array<string, array<string, mixed>> $contextual */
            $contextual = $property->getValue($this->container);
            return $contextual;
        }
        return [];
    }

    public function resolve(string $service)
    {
        if (!$this->container->bound($service)) {
            return null;
        }
        return $this->container->make($service);
    }

    public function inspectService(string $service): array
    {
        $class = $this->getClassForService($service);

        // Detect if the service is shared (singleton)
        $isShared = $this->container->isShared($service);

        $dependencies = [];
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
                    $dependencies[] = [
                        'name' => $param->getName(),
                        'type' => $typeName,
                        'isOptional' => $param->isOptional(),
                    ];
                }
            }
        }

        return [
            'class' => $class ?? null,
            'interfaces' => $class && class_exists($class) ? array_values(class_implements($class)) : [],
            'constructor_dependencies' => $dependencies,
            'dependencies' => $this->getDependencies($service),
            'bindingHistory' => $this->getBindingHistory($service),
            'resolved' => $this->resolve($service),
            'shared' => $isShared,
        ];
    }

    /**
     * Attempt to resolve the class name for a given service.
     */
    protected function getClassForService(string $service): ?string
    {
        // Try to resolve the concrete from the container
        if ($this->container->bound($service)) {
            $concrete = $this->container->getBindings()[$service]['concrete'] ?? null;
            if (is_string($concrete) && class_exists($concrete)) {
                return $concrete;
            }
            // If concrete is a closure, try to resolve and get its class
            try {
                $instance = $this->container->make($service);
                if (is_object($instance)) {
                    return get_class($instance);
                }
            } catch (Throwable $e) {
                // Could not resolve, return null
                return null;
            }
        }
        // If not bound, maybe it's a class name itself
        if (class_exists($service)) {
            return $service;
        }
        return null;
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
        $bindings = $this->getBindings();
        $counts = [];
        $duplicates = [];
        foreach (array_keys($bindings) as $service) {
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

    public function getTaggedServices(): array
    {
        $reflection = new \ReflectionClass($this->container);
        if ($reflection->hasProperty('tags')) {
            $property = $reflection->getProperty('tags');
            $property->setAccessible(true);
            /** @var array<string, array<string>> $tags */
            $tags = $property->getValue($this->container);
            return $tags;
        }
        return [];
    }

    public function setMutationDispatcher(MutationEventDispatcher $dispatcher): void
    {
        $this->mutationDispatcher = $dispatcher;
    }

    protected function trackMutation(array $mutation): void
    {
        $this->mutations[] = $mutation;
        if ($this->mutationDispatcher) {
            $this->mutationDispatcher->dispatch($mutation);
        }
    }

    public function bind(string $abstract, $concrete = null, bool $shared = false)
    {
        $this->container->bind($abstract, $concrete, $shared);
        $mutation = [
            'timestamp' => microtime(true),
            'type' => 'binding',
            'action' => 'added',
            'service' => $abstract,
            'details' => ['concrete' => $concrete, 'shared' => $shared],
        ];
        $this->trackMutation($mutation);
    }

    public function unbind(string $abstract)
    {
        unset($this->container->getBindings()[$abstract]);
        $mutation = [
            'timestamp' => microtime(true),
            'type' => 'binding',
            'action' => 'removed',
            'service' => $abstract,
            'details' => [],
        ];
        $this->trackMutation($mutation);
    }

    public function alias(string $abstract, string $alias)
    {
        $this->container->alias($abstract, $alias);
        $mutation = [
            'timestamp' => microtime(true),
            'type' => 'alias',
            'action' => 'added',
            'service' => $alias,
            'details' => ['target' => $abstract],
        ];
        $this->trackMutation($mutation);
    }

    public function unalias(string $alias)
    {
        unset($this->container->getAliases()[$alias]);
        $mutation = [
            'timestamp' => microtime(true),
            'type' => 'alias',
            'action' => 'removed',
            'service' => $alias,
            'details' => [],
        ];
        $this->trackMutation($mutation);
    }

    public function getMutations(): array
    {
        return $this->mutations;
    }
}
