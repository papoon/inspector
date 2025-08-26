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
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter services by name, class, or interface');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filter = $input->getOption('filter');
        $services = $this->inspector->browseServices();

        if ($filter) {
            $filterLower = strtolower($filter);
            $services = array_filter($services, function($s) use ($filterLower) {
                $details = $this->inspector->inspectService($s);
                // Match by service name
                if (stripos($s, $filterLower) !== false) {
                    return true;
                }
                // Match by class
                if (!empty($details['class']) && stripos((string)$details['class'], $filterLower) !== false) {
                    return true;
                }
                // Match by interfaces
                if (!empty($details['interfaces'])) {
                    foreach ($details['interfaces'] as $iface) {
                        if (stripos((string)$iface, $filterLower) !== false) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        $output->writeln("Registered services:");
        foreach ($services as $service) {
            $output->writeln("- $service");
            $details = $this->inspector->inspectService($service);
            if (!empty($details['constructor_dependencies'])) {
                $output->writeln("  Constructor dependencies:");
                foreach ($details['constructor_dependencies'] as $dep) {
                    $output->writeln("    - {$dep['name']}: {$dep['type']}" . ($dep['isOptional'] ? ' (optional)' : ''));
                }
            }
        }
        return Command::SUCCESS;
    }
}
