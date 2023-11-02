<?php

declare(strict_types=1);

namespace Local;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LocalCommand extends Command
{
    /** @var string|null */
    protected static $commandName = 'local:command';

    public function configure(): void
    {
        $this->setDescription('local command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
