<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class CheckAutowiringCommand extends Command
{
    protected static $defaultName = 'services:autowired';
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
        $adapter = $this->inspector->getAdapter();
        if (!method_exists($adapter, 'isAutowired')) {
            $output->writeln('<error>Autowiring info not supported by this adapter.</error>');
            return Command::FAILURE;
        }
        $isAutowired = $adapter->isAutowired($service);
        $output->writeln($isAutowired
            ? "<info>Service '$service' is autowired.</info>"
            : "<comment>Service '$service' is NOT autowired.</comment>");
        return Command::SUCCESS;
    }
}
