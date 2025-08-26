<?php

namespace Inspector;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

class ServiceReflectionHelper
{
    /**
     * @param class-string $class
     * @return array{name: string, type: string|null, isOptional: bool}[]
     */
    public static function getConstructorDependencies(string $class): array
    {
        $dependencies = [];
        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();
            if ($constructor) {
                foreach ($constructor->getParameters() as $param) {
                    $type = $param->getType();
                    $typeName = null;
                    if ($type instanceof ReflectionNamedType) {
                        $typeName = $type->getName();
                    } elseif ($type instanceof ReflectionUnionType) {
                        // For union types, join all names with '|'
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
        return $dependencies;
    }

    /**
     * @param class-string|null $class
     * @return array<string>
     */
    public static function getInterfaces(?string $class): array
    {
        return $class && class_exists($class) ? array_values(class_implements($class)) : [];
    }
}
