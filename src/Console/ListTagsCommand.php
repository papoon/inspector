<?php

declare(strict_types=1);

namespace Inspector\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inspector\Inspector;

class ListTagsCommand extends Command
{
    protected static $defaultName = 'services:tags';
    private Inspector $inspector;

    public function __construct(Inspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adapter = $this->inspector->getAdapter();
        if (!method_exists($adapter, 'getTags')) {
            $output->writeln('<error>Tags not supported by this adapter.</error>');
            return Command::FAILURE;
        }

        $tags = $adapter->getTags();
        if (empty($tags)) {
            $output->writeln('No tags found.');
            return Command::SUCCESS;
        }

        foreach ($tags as $tag => $services) {
            $output->writeln("<info>Tag: $tag</info>");
            foreach ($services as $service) {
                $output->writeln("  - $service");
            }
        }
        return Command::SUCCESS;
    }
}