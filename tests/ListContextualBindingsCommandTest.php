<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\ListContextualBindingsCommand;
use Illuminate\Container\Container;

class ListContextualBindingsCommandTest extends TestCase
{
    public function testListContextualBindingsCommandOutputsBindings(): void
    {
        $container = new Container();
        $container->when('ConsumerClass')
            ->needs('SomeInterface')
            ->give('ConcreteClass');
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListContextualBindingsCommand($inspector));

        $command = $application->find('services:contextual');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Abstract: SomeInterface', $output);
        $this->assertStringContainsString('Context: ConsumerClass', $output);
        $this->assertStringContainsString('Concrete: ConcreteClass', $output);
    }

    public function testListContextualBindingsCommandOutputsNoBindingsIfEmpty(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListContextualBindingsCommand($inspector));

        $command = $application->find('services:contextual');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No contextual bindings found.', $output);
    }
}