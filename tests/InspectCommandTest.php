<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Illuminate\Container\Container;

// Require command classes only, not the CLI entrypoint
require_once __DIR__ . '/../src/Console/Commands.php';

class InspectCommandTest extends TestCase
{
    public function testListServicesCommandOutputsServices(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);
        $application = new Application();
        $application->add(new ListServicesCommand($inspector));

        $command = $application->find('list');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Registered services:', $output);
        $this->assertStringContainsString('foo', $output);
    }

    public function testInspectServiceCommandOutputsDetails(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new InspectServiceCommand($inspector));

        $command = $application->find('inspect');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['service' => 'foo']);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('bar', $output);
    }
}
