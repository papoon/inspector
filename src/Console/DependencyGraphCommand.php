<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class DependencyGraphCommand extends Command
{
    protected static $defaultName = 'services:graph';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $services = $this->inspector->browseServices();
        foreach ($services as $service) {
            $deps = $this->inspector->inspectService($service)['dependencies'];
            $output->writeln("$service:");
            foreach ($deps as $dep) {
                $output->writeln("  -> $dep");
            }
        }
        return Command::SUCCESS;
    }
}
