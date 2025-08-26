<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Inspector\Adapters\PsrAdapter;

class DummyPsrDep
{
    public function __construct(public string $foo, public int $bar = 42) {}
}

class SimplePsrContainer implements ContainerInterface
{
    public function get($id)
    {
        if ($id === 'dummy') {
            return new DummyPsrDep('foo', 42);
        }
        throw new NotFoundException();
    }
    public function has(string $id): bool
    {
        return $id === 'dummy';
    }
}

class PsrAdapterTest extends TestCase
{
    public function testInspectServiceReturnsConstructorDependencies(): void
    {
        $container = new SimplePsrContainer();
        $adapter = new PsrAdapter($container);

        $details = $adapter->inspectService('dummy');
        $deps = $details['constructor_dependencies'];

        $this->assertNotEmpty($deps);
        $this->assertEquals('foo', $deps[0]['name']);
        $this->assertEquals('string', $deps[0]['type']);
        $this->assertFalse($deps[0]['isOptional']);

        $this->assertEquals('bar', $deps[1]['name']);
        $this->assertEquals('int', $deps[1]['type']);
        $this->assertTrue($deps[1]['isOptional']);
    }
}

class NotFoundException extends \Exception {}