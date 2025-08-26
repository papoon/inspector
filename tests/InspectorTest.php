<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Illuminate\Container\Container;

class InspectorTest extends TestCase
{
    public function testInspectServiceReturnsArrayWithKeys(): void
    {
        $container = new Container();
        // Register a dummy service so it can be resolved
        $container->bind('test', fn () => 'dummy');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $result = $inspector->inspectService('test');
        $this->assertArrayHasKey('dependencies', $result);
        $this->assertArrayHasKey('bindingHistory', $result);
        $this->assertArrayHasKey('resolved', $result);
    }

    public function testLaravelAdapterImplementsInterface(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);

        $this->assertInstanceOf(\Inspector\AdapterInterface::class, $adapter);
    }

    public function testInspectorConstructorSetsAdapter(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $reflection = new ReflectionClass($inspector);
        $property = $reflection->getProperty('adapter');
        $property->setAccessible(true);

        $this->assertSame($adapter, $property->getValue($inspector));
    }

    public function testInspectorDetectsRegisteredServices(): void
    {
        $container = new Container();

        // Register some services
        $container->bind('foo', fn () => 'bar');
        $container->bind('baz', fn () => 'qux');

        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $services = $inspector->browseServices();

        // Assert that registered services are detected
        $this->assertContains('foo', $services);
        $this->assertContains('baz', $services);
        $this->assertCount(2, $services);
    }
}
