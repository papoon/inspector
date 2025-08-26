<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class CircularDependencyCommand extends Command
{
    protected static $defaultName = 'services:circular';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $services = $this->inspector->browseServices();
        $visited = [];
        $stack = [];
        $circular = [];

        $check = function (string $service) use (&$check, &$visited, &$stack, &$circular) {
            // These checks are correct: $stack and $visited start empty, but will fill as recursion proceeds.
            if (in_array($service, $stack, true)) {
                $circular[] = implode(' -> ', array_merge($stack, [$service]));
                return;
            }
            if (in_array($service, $visited, true)) {
                return;
            }
            $stack[] = $service;
            $deps = $this->inspector->inspectService($service)['dependencies'];
            foreach ($deps as $dep) {
                $check($dep);
            }
            array_pop($stack);
            $visited[] = $service;
        };

        foreach ($services as $service) {
            $check((string)$service);
        }

        if ($circular) {
            $output->writeln('<error>Circular dependencies detected:</error>');
            foreach ($circular as $cycle) {
                $output->writeln($cycle);
            }
            return Command::FAILURE;
        }
        $output->writeln('<info>No circular dependencies detected.</info>');
        return Command::SUCCESS;
    }
}
