<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Inspector\Adapters\SymfonyAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SymfonyAdapterTest extends TestCase
{
    public function testGetServicesReturnsRegisteredServices(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('foo', new Definition(stdClass::class));
        $container->setDefinition('bar', new Definition(DateTimeImmutable::class));

        $adapter = new SymfonyAdapter($container);
        $services = $adapter->getServices();

        $this->assertContains('foo', $services);
        $this->assertContains('bar', $services);
        $this->assertCount(3, $services);
    }

    public function testGetAliasesReturnsAliases(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('foo', new Definition(stdClass::class));
        $container->setAlias('bar', 'foo');

        $adapter = new SymfonyAdapter($container);
        $aliases = $adapter->getAliases();

        $this->assertArrayHasKey('bar', $aliases);
        $this->assertSame('foo', $aliases['bar']);
    }

    public function testGetBindingsReturnsBindings(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('foo', new Definition(stdClass::class));

        $adapter = new SymfonyAdapter($container);
        $bindings = $adapter->getBindings();

        $this->assertArrayHasKey('foo', $bindings);
        $this->assertSame(stdClass::class, $bindings['foo']['concrete']);
        $this->assertTrue($bindings['foo']['shared']);
    }

    public function testGetDependenciesReturnsArguments(): void
    {
        $container = new ContainerBuilder();
        $def = new Definition(stdClass::class);
        $def->setArguments(['bar', 'baz']);
        $container->setDefinition('foo', $def);

        $adapter = new SymfonyAdapter($container);
        $deps = $adapter->getDependencies('foo');

        $this->assertContains('bar', $deps);
        $this->assertContains('baz', $deps);
    }

    public function testGetBindingHistoryReturnsEmptyArray(): void
    {
        $container = new ContainerBuilder();
        $adapter = new SymfonyAdapter($container);

        $history = $adapter->getBindingHistory('foo');
        $this->assertEmpty($history);
    }

    public function testResolveReturnsServiceInstance(): void
    {
        $container = new ContainerBuilder();
        $def = new Definition(stdClass::class);
        $def->setPublic(true); // Make the service public
        $container->setDefinition('foo', $def);
        $container->compile();

        $adapter = new SymfonyAdapter($container);
        $resolved = $adapter->resolve('foo');

        $this->assertInstanceOf(stdClass::class, $resolved);
    }

    public function testResolveReturnsNullForUnboundService(): void
    {
        $container = new ContainerBuilder();
        $adapter = new SymfonyAdapter($container);

        $resolved = $adapter->resolve('not_bound');
        $this->assertNull($resolved);
    }
}
