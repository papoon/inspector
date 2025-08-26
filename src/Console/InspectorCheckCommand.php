<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class InspectorCheckCommand extends Command
{
    protected static $defaultName = 'inspector:check';

    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure()
    {
        $this->setDescription('Checks for duplicate bindings and alias loops.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $duplicates = $this->inspector->getDuplicateBindings();
        $loops = $this->inspector->getAliasLoops();

        if ($duplicates) {
            $output->writeln('<error>Duplicate bindings detected:</error>');
            foreach ($duplicates as $service) {
                $output->writeln("- $service");
            }
        } else {
            $output->writeln('<info>No duplicate bindings detected.</info>');
        }

        if ($loops) {
            $output->writeln('<error>Alias loops detected:</error>');
            foreach ($loops as $loop) {
                $output->writeln('- ' . implode(' -> ', $loop));
            }
        } else {
            $output->writeln('<info>No alias loops detected.</info>');
        }

        $tagged = $this->inspector->getTaggedServices();
        if ($tagged) {
            $output->writeln('<info>Tagged services:</info>');
            foreach ($tagged as $tag => $services) {
                $output->writeln("$tag: " . implode(', ', $services));
            }
        }

        return Command::SUCCESS;
    }
}
