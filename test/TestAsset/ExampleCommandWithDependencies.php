<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommandWithDependencies extends Command
{
    /** @var string|null */
    protected static $defaultName = 'example:command-with-deps';

    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements
    private ExampleDependency $dependency;

    public function __construct(ExampleDependency $dependency)
    {
        $this->dependency = $dependency;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Test command with dependencies');
        $this->setHelp('Execute a test command that includes dependencies');
        $this->addOption(
            'string',
            's',
            InputOption::VALUE_REQUIRED,
            'A string option',
            $this->dependency->getDefault()
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
