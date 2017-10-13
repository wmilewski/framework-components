<?php

namespace Framework\Container;

use Closure;

class Container
{
    protected $bindings = [];

    public function bind(string $name, Closure $closure, bool $shared = false)
    {
        $this->bindings[$name] = new Binding($this, $closure, $shared);
    }

    public function bindClass(string $name, string $class, bool $shared = false)
    {
        $this->bindings[$name] = new ClassBinding($this, $class, $shared);
    }

    public function make(string $name, ...$parameters)
    {
        if ($this->has($name)) {
            return $this->get($name)->resolve(...$parameters);
        }

        return $this->assumeInstantiableClassAndResolve($name);
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

    protected function get(string $name): Binding
    {
        return $this->bindings[$name];
    }

    protected function assumeInstantiableClassAndResolve(string $name)
    {
        return (new ClassBinding($this, $name))->resolve();
    }
}
