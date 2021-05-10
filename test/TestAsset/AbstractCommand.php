<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

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
        Assert::stringNotEmpty(static::$defaultName);
        Assert::stringNotEmpty($this->argName);
        Assert::stringNotEmpty($this->optName);

        $name = static::$defaultName ?? '';

        $this->setDescription('Description of ' . $name);
        $this->addArgument($this->argName, InputArgument::OPTIONAL);
        $this->addOption($this->optName, null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $arg */
        $arg = $input->getArgument($this->argName) ?: '';

        /** @var string $opt */
        $opt = $input->getOption($this->optName) ?: '';

        $output->writeln(
            static::class
            . ': arg=' . $arg
            . ', opt=' . $opt
        );

        return $this->statusCode;
    }
}
