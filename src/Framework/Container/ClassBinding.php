<?php

namespace Framework\Container;

use Closure;
use ReflectionClass;
use ReflectionParameter;

class ClassBinding extends Binding
{
    public function __construct(Container $container, string $class, $shared = false)
    {
        parent::__construct($container, $this->wrapIntoClosure($class), $shared);
    }

    protected function wrapIntoClosure(string $class): Closure
    {
        return function () use ($class) {
            return $this->resolveInstance($class);
        };
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
            $this->resolveDependencies($dependencies)
        );
    }

    protected function resolveDependencies(array $dependencies): array
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
            return $this->container->make($parameter->getClass()->getName());
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
