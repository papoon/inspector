<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\CircularDependencyCommand;
use Illuminate\Container\Container;

class CircularDependencyCommandTest extends TestCase
{
    public function testNoCircularDependencies(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new CircularDependencyCommand($inspector));

        $command = $application->find('services:circular');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No circular dependencies detected.', $output);
    }
}
