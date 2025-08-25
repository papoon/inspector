<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ListServicesCommand extends Command
{
    protected static $defaultName = 'services:list';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('List registered services')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter services by name')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format (text|json)', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filter = $input->getOption('filter');
        $format = $input->getOption('format');
        $services = $this->inspector->browseServices();

        if ($filter) {
            $services = array_filter($services, fn($s) => stripos($s, $filter) !== false);
        }

        if ($format === 'json') {
            $output->writeln(json_encode(array_values($services), JSON_PRETTY_PRINT));
        } else {
            $output->writeln("Registered services:");
            foreach ($services as $service) {
                $output->writeln("- $service");
            }
        }
        return Command::SUCCESS;
    }
}
