<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommandWithDependencies extends Command
{
    /** @var string */
    protected static $defaultName = 'example:command-with-deps';

    /** @var ExampleDependency */
    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements
    private $dependency;

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
