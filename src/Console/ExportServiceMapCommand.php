<?php

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;
use Symfony\Component\Yaml\Yaml;

class ExportServiceMapCommand extends Command
{
    protected static $defaultName = 'inspector:export-map';

    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure(): void
    {
        $this->addArgument('format', InputArgument::OPTIONAL, 'Export format: json|yaml|md', 'json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = $input->getArgument('format');
        $map = [];
        foreach ($this->inspector->browseServices() as $service) {
            $map[$service] = $this->inspector->inspectService($service);
        }

        if ($format === 'json') {
            $output->writeln(json_encode($map, JSON_PRETTY_PRINT));
        } elseif ($format === 'yaml') {
            $output->writeln(Yaml::dump($map, 4, 2));
        } elseif ($format === 'md') {
            $output->writeln("# Service Map\n");
            foreach ($map as $name => $detail) {
                $output->writeln("## `$name`");
                if (!empty($detail['class'])) {
                    $output->writeln("- **Class:** `{$detail['class']}`");
                }
                if (!empty($detail['interfaces'])) {
                    $output->writeln('- **Interfaces:** ' . implode(', ', array_map(fn ($i) => "`$i`", $detail['interfaces'])));
                }
                if (!empty($detail['constructor_dependencies'])) {
                    $output->writeln('- **Constructor dependencies:**');
                    foreach ($detail['constructor_dependencies'] as $dep) {
                        $output->writeln("  - `{$dep['name']}`: `{$dep['type']}`" . ($dep['isOptional'] ? ' _(optional)_' : ''));
                    }
                }
                $output->writeln('');
            }
        } else {
            $output->writeln("<error>Unknown format: $format</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
