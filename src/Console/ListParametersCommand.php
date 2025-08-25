<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ListParametersCommand extends Command
{
    protected static $defaultName = 'services:parameters';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adapter = $this->inspector->getAdapter();
        if (!method_exists($adapter, 'getParameters')) {
            $output->writeln('<error>Parameters not supported by this adapter.</error>');
            return Command::FAILURE;
        }
        $parameters = $adapter->getParameters();
        if (empty($parameters)) {
            $output->writeln('No parameters found.');
            return Command::SUCCESS;
        }
        foreach ($parameters as $key => $value) {
            $output->writeln("$key: " . (is_scalar($value) ? $value : json_encode($value)));
        }
        return Command::SUCCESS;
    }
}
