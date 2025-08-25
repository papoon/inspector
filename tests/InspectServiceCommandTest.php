<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\InspectServiceCommand;
use Illuminate\Container\Container;

class InspectServiceCommandTest extends TestCase
{
    public function testInspectServiceCommandOutputsDetails(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new InspectServiceCommand($inspector));

        $command = $application->find('services:inspect');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['service' => 'foo']);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Service: foo', $output);
        $this->assertStringContainsString('Dependencies:', $output);
        $this->assertStringContainsString('Binding History:', $output);
        $this->assertStringContainsString('Resolved Value:', $output);
        $this->assertStringContainsString('bar', $output);
    }

    public function testInspectServiceCommandWithMissingService(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new InspectServiceCommand($inspector));

        $command = $application->find('services:inspect');
        $commandTester = new CommandTester($command);

        // Simulate missing service argument
        $commandTester->execute(['service' => '']);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No service name provided.', $output);
    }
}