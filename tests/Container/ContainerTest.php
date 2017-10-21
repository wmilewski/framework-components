<?php

namespace Framework\Tests\Container;

use Framework\Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container;
    }

    /** @test */
    public function it_calls_object_methods_and_resolves_their_parameters()
    {
        $this->container->bindClass(
            StubInterfaceForDependentMethod::class,
            StubImplementationForDependentMethod::class
        );

        $object = new StubClassWithDependentMethod;

        $this->container->callObjectMethod($object, 'stubMethod');

        $this->assertInstanceOf(StubImplementationForDependentMethod::class, $object->paramOne);
        $this->assertEquals(999, $object->paramTwo);
    }
}

interface StubInterfaceForDependentMethod
{

}

class StubImplementationForDependentMethod implements StubInterfaceForDependentMethod
{

}

class StubClassWithDependentMethod
{
    public $paramOne;
    public $paramTwo;

    public function stubMethod(StubInterfaceForDependentMethod $paramOne, int $paramTwo = 999)
    {
        $this->paramOne = $paramOne;
        $this->paramTwo = $paramTwo;
    }
}
