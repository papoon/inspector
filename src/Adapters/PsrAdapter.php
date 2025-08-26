<?php

declare(strict_types=1);

namespace Inspector\Adapters;

use Inspector\AdapterInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Throwable;

class PsrAdapter implements AdapterInterface
{
    protected ContainerInterface $container;

    
    public function getTaggedServices(): array
    {
        // PSR containers do not expose tags, so return empty
        return [];
    }

    /**
     * @return array<string>
     */
    public function findDuplicateBindings(): array
    {
        // PSR containers do not expose binding details, so return empty
        return [];
    }

    /**
     * @return array<array<string>>
     */
    public function findAliasLoops(): array
    {
        // PSR containers do not expose alias details, so return empty
        return [];
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getServices(): array
    {
        // Try common methods for service discovery
        if (method_exists($this->container, 'getServiceIds')) {
            return $this->container->getServiceIds();
        }
        if (method_exists($this->container, 'keys')) {
            return $this->container->keys();
        }
        // If not supported, return empty
        return [];
    }

    /**
     * @return array<string>
     */
    public function getAliases(): array
    {
        return [];
    }

    /** @return array<string, array{concrete: mixed, shared: bool}> */
    public function getBindings(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getDependencies(string $service): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getBindingHistory(string $service): array
    {
        return [];
    }

    public function resolve(string $service): mixed
    {
        if ($this->container->has($service)) {
            return $this->container->get($service);
        }
        return null;
    }

    public function getContainerType(): string
    {
        return get_class($this->container);
    }

    public function inspectService(string $service): array
    {
        $class = null;
        $constructorDependencies = [];

        // Try to resolve the service and get its class
        if ($this->container->has($service)) {
            $instance = $this->container->get($service);
            if (is_object($instance)) {
                $class = get_class($instance);

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
            'shared' => null, // or false
        ];
    }

    /**
     * @param string $service
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
        return null;
    }
}
