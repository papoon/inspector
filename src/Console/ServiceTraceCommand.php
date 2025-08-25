<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ServiceTraceCommand extends Command
{
    protected static $defaultName = 'services:trace';
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
        $trace = [];
        $resolveTrace = function ($svc) use (&$resolveTrace, &$trace) {
            $trace[] = $svc;
            $deps = $this->inspector->inspectService($svc)['dependencies'];
            foreach ($deps as $dep) {
                $resolveTrace($dep);
            }
        };
        $resolveTrace($service);
        $output->writeln('Resolution trace:');
        foreach ($trace as $svc) {
            $output->writeln(" - $svc");
        }
        return Command::SUCCESS;
    }
}
