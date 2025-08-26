<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Inspector\Console\ExportServiceMapCommand;
use Illuminate\Container\Container;
use Symfony\Component\Yaml\Yaml;

class DummyExportService
{
    public function __construct()
    {
    }
}

class ExportServiceMapCommandTest extends TestCase
{
    private Application $application;
    private Inspector $inspector;

    protected function setUp(): void
    {
        $container = new Container();
        $container->bind('dummy', DummyExportService::class);
        $adapter = new LaravelAdapter($container);
        $this->inspector = new Inspector($adapter);

        $this->application = new Application();
        $this->application->add(new ExportServiceMapCommand($this->inspector));
    }

    public function testExportJson(): void
    {
        $command = $this->application->find('inspector:export-map');
        $tester = new CommandTester($command);
        $tester->execute(['format' => 'json']);

        $output = $tester->getDisplay();
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('dummy', $data);
        $this->assertArrayHasKey('class', $data['dummy']);
    }

    public function testExportYaml(): void
    {
        $command = $this->application->find('inspector:export-map');
        $tester = new CommandTester($command);
        $tester->execute(['format' => 'yaml']);

        $output = $tester->getDisplay();
        $data = Yaml::parse($output);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('dummy', $data);
        $this->assertArrayHasKey('class', $data['dummy']);
    }

    public function testExportMarkdown(): void
    {
        $command = $this->application->find('inspector:export-map');
        $tester = new CommandTester($command);
        $tester->execute(['format' => 'md']);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('# Service Map', $output);
        $this->assertStringContainsString('## `dummy`', $output);
        $this->assertStringContainsString('**Class:**', $output);
    }
}
