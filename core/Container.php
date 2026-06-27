<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;
use ReflectionNamedType;

class Container
{
    private array $bindings  = [];
    private array $instances = [];

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        // Will be cached on first resolve
    }

    public function make(string $abstract): object
    {
        // Return cached singleton
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        $instance = $this->build($concrete);

        $this->instances[$abstract] = $instance;

        return $instance;
    }

    private function build(string $concrete): object
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException(
                    "Cannot resolve parameter [{$param->getName()}] for [{$concrete}]."
                );
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
