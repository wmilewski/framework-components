<?php

namespace Framework\Tests\Container;

use Framework\Container\BindingResolutionException;
use Framework\Container\Container;
use PHPUnit\Framework\TestCase;

class ClassBindingTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container;
    }

    /** @test */
    public function it_binds_and_resolves_classes()
    {
        $this->container->bindClass('name', StubClass::class);

        $this->assertInstanceOf(StubClass::class, $this->container->make('name'));
    }

    /** @test */
    public function it_instantiates_bound_class_every_time_by_default()
    {
        $this->container->bindClass('name', StubClass::class);

        $this->assertNotSame(
            $this->container->make('name'),
            $this->container->make('name')
        );
    }

    /** @test */
    public function it_resolves_singletons_only_once()
    {
        $this->container->singletonClass('name', StubClass::class);

        $this->assertSame(
            $this->container->make('name'),
            $this->container->make('name')
        );
    }

    /** @test */
    public function it_automatically_resolves_instantiable_classes()
    {
        $this->assertInstanceOf(StubClass::class, $this->container->make(StubClass::class));
    }

    /** @test */
    public function it_refuses_attempts_to_resolve_unbound_and_not_instantiable_entities()
    {
        $this->expectException(BindingResolutionException::class);

        $this->container->make(StubInterface::class);
    }

    /** @test */
    public function it_resolves_nested_dependencies()
    {
        $this->container->bindClass(StubInterface::class, StubInterfaceImplementation::class);
        $object = $this->container->make(StubClassWithDependencies::class);

        $this->assertInstanceOf(StubDependencyClass::class, $object->dependency);
        $this->assertInstanceOf(StubInterfaceImplementation::class, $object->dependency->implementation);
    }

    /** @test */
    public function it_resolves_optional_primitives_as_their_given_default_values()
    {
        $object = $this->container->make(StubClassWithOptionalPrimitives::class);

        $this->assertNull($object->nullable);
        $this->assertSame(1, $object->primitive);
    }

    /** @test */
    public function it_refuses_attempts_to_resolve_required_primitives()
    {
        $this->expectException(BindingResolutionException::class);

        $this->container->make(StubClassWithRequiredPrimitives::class);
    }
}

class StubClass
{

}

interface StubInterface
{

}

class StubInterfaceImplementation implements StubInterface
{

}

class StubDependencyClass
{
    public $implementation;

    public function __construct(StubInterface $implementation)
    {
        $this->implementation = $implementation;
    }
}

class StubClassWithDependencies
{
    public $dependency;

    public function __construct(StubDependencyClass $dependency)
    {
        $this->dependency = $dependency;
    }
}

class StubClassWithOptionalPrimitives
{
    public $nullable;
    public $primitive;

    public function __construct(int $nullable = null, int $primitive = 1)
    {
        $this->nullable = $nullable;
        $this->primitive = $primitive;
    }
}

class StubClassWithRequiredPrimitives
{
    public function __construct(int $required)
    {

    }
}
