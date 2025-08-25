<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Illuminate\Container\Container;

class InspectorTest extends TestCase
{
    public function testInspectServiceReturnsArrayWithKeys()
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $result = $inspector->inspectService('test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('dependencies', $result);
        $this->assertArrayHasKey('bindingHistory', $result);
        $this->assertArrayHasKey('resolved', $result);
    }

    public function testLaravelAdapterImplementsInterface()
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);

        $this->assertInstanceOf(\Inspector\AdapterInterface::class, $adapter);
    }

    public function testInspectorConstructorSetsAdapter()
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $reflection = new ReflectionClass($inspector);
        $property = $reflection->getProperty('adapter');
        $property->setAccessible(true);

        $this->assertSame($adapter, $property->getValue($inspector));
    }

    public function testBrowseServicesReturnsArray()
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $this->assertIsArray($inspector->browseServices());
    }
}
