<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $services = $this->inspector->browseServices();
        $output->writeln("Registered services:");
        foreach ($services as $service) {
            $output->writeln("- $service");
        }
        return Command::SUCCESS;
    }
}