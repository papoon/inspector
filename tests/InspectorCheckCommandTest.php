<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Console\InspectorCheckCommand;

class DummyAdapter implements \Inspector\AdapterInterface
{
    public function getTaggedServices(): array
    {
        return [];
    }

    public function getBindingHistory(string $service): array
    {
        return [];
    }

    public function getDependencies(string $service): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [
            'a' => 'b',
            'b' => 'c',
            'c' => 'a', // loop: a -> b -> c -> a
        ];
    }

    public function getBindings(): array
    {
        return [
            'foo' => ['concrete' => 'FooClass', 'shared' => true],
            'bar' => ['concrete' => 'BarClass', 'shared' => true],
        ];
    }

    public function getServices(): array
    {
        return ['foo', 'bar', 'foo'];
    }

    public function inspectService(string $service): array
    {
        return [
            'class' => null,
            'interfaces' => [],
            'constructor_dependencies' => [],
            'dependencies' => [],
            'bindingHistory' => [],
            'resolved' => null,
            'shared' => null,
        ];
    }

    public function resolve(string $service)
    {
        return null;
    }

    public function getResolutionError(string $service): ?array
    {
        return null;
    }

    public function findDuplicateBindings(): array
    {
        return ['foo'];
    }

    public function findAliasLoops(): array
    {
        return [['a', 'b', 'c', 'a']];
    }
}

class InspectorCheckCommandTest extends TestCase
{
    public function testDetectsDuplicateBindingsAndAliasLoops(): void
    {
        $adapter = new DummyAdapter();
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new InspectorCheckCommand($inspector));
        $command = $application->find('inspector:check');
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Duplicate bindings detected:', $output);
        $this->assertStringContainsString('- foo', $output);
        $this->assertStringContainsString('Alias loops detected:', $output);
        $this->assertStringContainsString('a -> b -> c -> a', $output);
    }

    public function testNoDuplicatesOrLoops(): void
    {
        $adapter = $this->getMockBuilder(DummyAdapter::class)
            ->onlyMethods(['findDuplicateBindings', 'findAliasLoops'])
            ->getMock();
        $adapter->method('findDuplicateBindings')->willReturn([]);
        $adapter->method('findAliasLoops')->willReturn([]);

        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new InspectorCheckCommand($inspector));
        $command = $application->find('inspector:check');
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('No duplicate bindings detected.', $output);
        $this->assertStringContainsString('No alias loops detected.', $output);
    }
}
