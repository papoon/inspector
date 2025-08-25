<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\ListTagsCommand;
use Illuminate\Container\Container;

class ListTagsCommandTest extends TestCase
{
    public function testListTagsCommandOutputsTags(): void
    {
        $container = new Container();
        $container->bind('foo', fn () => 'bar');
        $container->tag('foo', ['custom-tag']);
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListTagsCommand($inspector));

        $command = $application->find('services:tags');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Tag: custom-tag', $output);
        $this->assertStringContainsString('foo', $output);
    }

    public function testListTagsCommandOutputsNoTagsIfEmpty(): void
    {
        $container = new Container();
        $adapter = new LaravelAdapter($container);
        $inspector = new Inspector($adapter);

        $application = new Application();
        $application->add(new ListTagsCommand($inspector));

        $command = $application->find('services:tags');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No tags found.', $output);
    }
}
