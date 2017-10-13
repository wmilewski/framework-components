<?php

namespace Framework\Tests\Container;

use Framework\Container\Container;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClosureBindingTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container;
    }

    /** @test */
    public function it_binds_and_resolves_closures()
    {
        $this->container->bind('name', function () {
            return 'Wojciech';
        });

        $this->assertEquals('Wojciech', $this->container->make('name'));
    }

    /** @test */
    public function it_calls_bound_closures_every_time_by_default()
    {
        $this->container->bind('object', function () {
            return new stdClass;
        });

        $this->assertNotSame(
            $this->container->make('object'),
            $this->container->make('object')
        );
    }

    /** @test */
    public function it_resolves_singletons_only_once()
    {
        $this->container->singleton('object', function () {
            return new stdClass;
        });

        $this->assertSame(
            $this->container->make('object'),
            $this->container->make('object')
        );
    }

    /** @test */
    public function it_passes_only_the_container_instance_to_closure_by_default()
    {
        $this->container->bind('name', function (...$args) {
            $this->assertCount(1, $args);
            $this->assertSame($this->container, $args[0]);
        });

        $this->container->make('name');
    }

    /** @test */
    public function it_passes_all_given_extra_parameters_to_closure()
    {
        $this->container->bind('name', function (...$args) {
            $this->assertCount(4, $args);
            $this->assertSame(1, $args[1]);
            $this->assertSame(2, $args[2]);
            $this->assertSame(3, $args[3]);
        });

        $this->container->make('name', 1, 2, 3);
    }
}
