<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\SymfonyAdapter;
use Inspector\Console\ListParametersCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ListParametersCommandTest extends TestCase
{
    public function testListParametersCommandOutputsParameters(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('foo', 'bar');
        $container->setParameter('baz', ['a', 'b']);
        $adapter = new SymfonyAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListParametersCommand($inspector));

        $command = $application->find('services:parameters');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('foo: bar', $output);
        $this->assertStringContainsString('baz: ["a","b"]', $output);
    }

    public function testListParametersCommandOutputsNoParametersIfEmpty(): void
    {
        $container = new ContainerBuilder();
        $adapter = new SymfonyAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListParametersCommand($inspector));

        $command = $application->find('services:parameters');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No parameters found.', $output);
    }
}
