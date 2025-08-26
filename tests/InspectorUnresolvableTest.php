<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Illuminate\Container\Container;

class BrokenService
{
    // @phpstan-ignore-next-line
    public function __construct(NonExistentClass $foo)
    {
    }
}

class InspectorUnresolvableTest extends TestCase
{
    public function testFindUnresolvableServicesReturnsBrokenService(): void
    {
        $container = new Container();
        $container->bind('broken', BrokenService::class);
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $broken = $inspector->findUnresolvableServices();
        var_dump($broken);
        $this->assertContains('broken', $broken);
    }

    public function testFindUnresolvableServicesWithDetailsReturnsErrorInfo(): void
    {
        $container = new Container();
        $container->bind('broken', BrokenService::class);
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $brokenDetails = $inspector->findUnresolvableServicesWithDetails();
        $this->assertArrayHasKey('broken', $brokenDetails);
        $info = $brokenDetails['broken'];
        $this->assertArrayHasKey('type', $info);
        $this->assertArrayHasKey('message', $info);
        $this->assertArrayHasKey('file', $info);
        $this->assertArrayHasKey('line', $info);
        $this->assertInstanceOf(Throwable::class, $info['exception']);
        $this->assertStringContainsString('NonExistentClass', $info['exception']->getMessage());
    }
}
