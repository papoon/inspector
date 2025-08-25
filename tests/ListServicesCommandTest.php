<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\ListServicesCommand;
use Illuminate\Container\Container;

class ListServicesCommandTest extends TestCase
{
    public function testListServicesCommandOutputsRegisteredServices(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $container->bind('baz', fn () => 'qux');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListServicesCommand($inspector));

        $command = $application->find('services:list');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Registered services:', $output);
        $this->assertStringContainsString('foo', $output);
        $this->assertStringContainsString('baz', $output);
    }

    public function testListServicesCommandOutputsNoServicesIfEmpty(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListServicesCommand($inspector));

        $command = $application->find('services:list');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Registered services:', $output);
        // Should not list any services
        $this->assertEquals("Registered services:\n", $output);
    }
}