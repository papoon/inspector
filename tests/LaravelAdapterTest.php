<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Inspector\Adapters\LaravelAdapter;
use Illuminate\Container\Container;

class LaravelAdapterTest extends TestCase
{
    public function testGetServicesReturnsRegisteredServices(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $container->bind('baz', fn () => 'qux');

        $adapter = new LaravelAdapter($container);
        $services = $adapter->getServices();

        $this->assertContains('foo', $services);
        $this->assertContains('baz', $services);
        $this->assertCount(2, $services);
    }

    public function testGetAliasesReturnsAliases(): void
    {
        $container = new Container();
        $container->alias('foo', 'bar');

        $adapter = new LaravelAdapter($container);
        $aliases = $adapter->getAliases();

        $this->assertArrayHasKey('bar', $aliases);
        $this->assertSame('foo', $aliases['bar']);
    }

    public function testGetBindingsReturnsBindings(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');

        $adapter = new LaravelAdapter($container);
        $bindings = $adapter->getBindings();

        $this->assertArrayHasKey('foo', $bindings);
        $this->assertArrayHasKey('concrete', $bindings['foo']);
        $this->assertArrayHasKey('shared', $bindings['foo']);
    }

    public function testGetDependenciesReturnsEmptyArray(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);

        $dependencies = $adapter->getDependencies('foo');
        $this->assertEmpty($dependencies);
    }

    public function testGetBindingHistoryReturnsEmptyArray(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);

        $history = $adapter->getBindingHistory('foo');
        $this->assertEmpty($history);
    }

    public function testResolveReturnsBoundService(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');

        $adapter = new LaravelAdapter($container);
        $resolved = $adapter->resolve('foo');

        $this->assertSame('bar', $resolved);
    }

    public function testResolveReturnsNullForUnboundService(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);

        $resolved = $adapter->resolve('not_bound');
        $this->assertNull($resolved);
    }
}