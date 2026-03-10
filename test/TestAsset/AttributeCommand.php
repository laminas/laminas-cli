<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:my-command')]
final readonly class AttributeCommand
{
    public function __invoke(
        SymfonyStyle $io,
        #[Argument]
        #[Ask('What is your name?')]
        string $name,
    ): int {
        $io->write('Hello ' . $name);
        return Command::SUCCESS;
    }
}
