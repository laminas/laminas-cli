<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Laminas\Cli\Input\IntParam;
use Laminas\Cli\Input\ParamAwareInputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class ParamCommand extends AbstractParamAwareCommand
{
    protected function configure(): void
    {
        $this->addParam(
            (new IntParam('int-param'))
                ->setDescription('Param description')
                ->setRequiredFlag(true)
                ->setMin(1)
                ->setMax(10)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Assert::isInstanceOf($input, ParamAwareInputInterface::class);

        /** @var int $int */
        $int = $input->getParam('int-param');
        $output->writeln('Int param value: ' . $int);

        return 0;
    }
}
