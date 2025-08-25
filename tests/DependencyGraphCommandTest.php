<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\DependencyGraphCommand;
use Illuminate\Container\Container;

class DependencyGraphCommandTest extends TestCase
{
    public function testDependencyGraphOutputsGraph(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $container->bind('bar', fn () => 'baz');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new DependencyGraphCommand($inspector));

        $command = $application->find('services:graph');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('foo:', $output);
        $this->assertStringContainsString('bar:', $output);
    }
}