<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ListContextualBindingsCommand extends Command
{
    protected static $defaultName = 'services:contextual';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adapter = $this->inspector->getAdapter();
        if (!method_exists($adapter, 'getContextualBindings')) {
            $output->writeln('<error>Contextual bindings not supported by this adapter.</error>');
            return Command::FAILURE;
        }

        $contextual = $adapter->getContextualBindings();
        if (empty($contextual)) {
            $output->writeln('No contextual bindings found.');
            return Command::SUCCESS;
        }

        foreach ($contextual as $context => $abstracts) {
            foreach ($abstracts as $abstract => $concrete) {
                $output->writeln("<info>Abstract: $abstract</info>");
                $output->writeln("  Context: $context => Concrete: " . (is_string($concrete) ? $concrete : gettype($concrete)));
            }
        }
        return Command::SUCCESS;
    }
}
