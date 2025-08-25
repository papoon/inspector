<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class InspectServiceCommand extends Command
{
    protected static $defaultName = 'services:inspect';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Inspect a registered service in the container')
            ->addArgument('service', InputArgument::REQUIRED, 'Service name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = $input->getArgument('service');
        if (!$service) {
            $output->writeln('<error>No service name provided.</error>');
            return Command::FAILURE;
        }

        $detail = $this->inspector->inspectService($service);

        $output->writeln("<info>Service: {$service}</info>");
        $output->writeln('<comment>Dependencies:</comment>');
        foreach ($detail['dependencies'] as $dep) {
            $output->writeln(" - $dep");
        }
        $output->writeln('<comment>Binding History:</comment>');
        foreach ($detail['bindingHistory'] as $history) {
            $output->writeln(" - $history");
        }
        $output->writeln('<comment>Resolved Value:</comment>');
        $output->writeln(print_r($detail['resolved'], true));

        return Command::SUCCESS;
    }
}
