<?php

namespace Framework\Container;

use Closure;
use ReflectionClass;
use ReflectionParameter;

class Container
{
    /**
     * @var Binding[]
     */
    protected $bindings = [];

    public function bind(string $name, Closure $closure, bool $shared = false)
    {
        $this->bindings[$name] = new Binding($this, $closure, $shared);
    }

    public function bindClass(string $name, string $class, bool $shared = false)
    {
        $this->bindings[$name] = new Binding($this, function () use ($class) {
            return $this->resolveInstance($class);
        }, $shared);
    }

    public function make(string $name, ...$parameters)
    {
        if (isset($this->bindings[$name])) {
            return $this->bindings[$name]->resolve(...$parameters);
        }

        return $this->resolveInstance($name);
    }

    public function singleton(string $name, Closure $closure)
    {
        $this->bind($name, $closure, true);
    }

    public function singletonClass(string $name, string $class)
    {
        $this->bindClass($name, $class, true);
    }

    public function has(string $name): bool
    {
        return isset($this->bindings[$name]);
    }

    public function callObjectMethod($object, string $name)
    {
        $reflector = new \ReflectionMethod(get_class($object), $name);

        return $reflector->invokeArgs($object, $this->resolveParameters(
            $reflector->getParameters()
        ));
    }

    protected function resolveInstance(string $class)
    {
        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new BindingResolutionException("{$class} is not instantiable");
        }

        if (is_null($constructor = $reflector->getConstructor())) {
            return $reflector->newInstance();
        }

        $dependencies = $constructor->getParameters();

        return $reflector->newInstanceArgs(
            $this->resolveParameters($dependencies)
        );
    }

    protected function resolveParameters(array $dependencies): array
    {
        return array_map(function (ReflectionParameter $parameter) {
            return $parameter->getClass() ?
                $this->resolveClassParameter($parameter) :
                $this->resolvePrimitiveParameter($parameter);
        }, $dependencies);
    }

    protected function resolveClassParameter(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->getName());
        } catch (BindingResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    protected function resolvePrimitiveParameter(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new BindingResolutionException("Cannot resolve argument {$parameter}");
    }
}
