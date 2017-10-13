<?php

namespace Framework\Container;

use Closure;

class Binding
{
    protected $container;
    protected $closure;
    protected $shared;
    protected $sharedInstance;

    public function __construct(Container $container, Closure $closure, bool $shared = false)
    {
        $this->container = $container;
        $this->closure = $closure;
        $this->shared = $shared;
    }

    public function resolve(...$parameters)
    {
        if ($this->shared) {
            return $this->sharedInstance ?? $this->sharedInstance = $this->callClosure(...$parameters);
        }

        return $this->callClosure(...$parameters);
    }

    protected function callClosure(...$parameters)
    {
        $closure = $this->closure;

        return $closure($this->container, ...$parameters);
    }
}
