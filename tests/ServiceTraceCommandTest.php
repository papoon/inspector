<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\ServiceTraceCommand;
use Illuminate\Container\Container;

class ServiceTraceCommandTest extends TestCase
{
    public function testServiceTraceOutputsTrace(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $container->bind('bar', fn () => 'baz');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ServiceTraceCommand($inspector));

        $command = $application->find('services:trace');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['service' => 'foo']);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Resolution trace:', $output);
        $this->assertStringContainsString('foo', $output);
    }
}