<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ListServicesCommand extends Command
{
    protected static $defaultName = 'list';
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

class InspectServiceCommand extends Command
{
    protected static $defaultName = 'inspect';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure(): void
    {
        $this->addArgument('service', InputArgument::REQUIRED, 'Service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = $input->getArgument('service');
        $detail = $this->inspector->inspectService($service);
        $output->writeln(print_r($detail, true));
        return Command::SUCCESS;
    }
}