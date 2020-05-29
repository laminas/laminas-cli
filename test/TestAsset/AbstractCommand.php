<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /** @var string */
    protected $argName;

    /** @var string */
    protected $optName;

    /** @var int */
    protected $statusCode;

    public function __construct(int $statusCode = 0)
    {
        parent::__construct(self::$defaultName);
        $this->statusCode = $statusCode;
    }

    protected function configure(): void
    {
        $this->setDescription('Description of ' . static::$defaultName);
        $this->addArgument($this->argName, InputArgument::OPTIONAL);
        $this->addOption($this->optName, null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            static::class
            . ': arg=' . $input->getArgument($this->argName)
            . ', opt=' . $input->getOption($this->optName)
        );

        return $this->statusCode;
    }
}
