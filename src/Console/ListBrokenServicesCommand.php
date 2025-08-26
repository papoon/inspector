<?php

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ListBrokenServicesCommand extends Command
{
    protected static $defaultName = 'inspector:list-broken';

    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $broken = $this->inspector->findUnresolvableServices();
        if ($broken) {
            $output->writeln('<error>Unresolvable services:</error>');
            foreach ($broken as $service) {
                $output->writeln("- $service");
            }
            return Command::FAILURE;
        }
        $output->writeln('<info>All services are resolvable.</info>');
        return Command::SUCCESS;
    }
}
