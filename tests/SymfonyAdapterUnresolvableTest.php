<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Inspector\Adapters\SymfonyAdapter;

class BrokenSymfonyService
{
    // @phpstan-ignore-next-line
    public function __construct(public NonExistentClass $foo)
    {
    }
}

class SymfonyAdapterUnresolvableTest extends TestCase
{
    public function testFindUnresolvableServicesWithDetailsReturnsErrorInfo(): void
    {
        $container = new ContainerBuilder();
        $def = new Definition(BrokenSymfonyService::class);
        $def->setAutowired(true); // Enable autowiring for this service
        $container->setDefinition('broken', $def);

        $adapter = new SymfonyAdapter($container);

        $error = $adapter->getResolutionError('broken');
        $this->assertIsArray($error, 'Expected error details array');
        $this->assertArrayHasKey('type', $error);
        $this->assertArrayHasKey('message', $error);
        $this->assertArrayHasKey('exception', $error);
        $this->assertInstanceOf(Throwable::class, $error['exception']);
    }
}
