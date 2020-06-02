<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\Cli\Command\ParamAwareCommandTrait;
use Laminas\Cli\Input\IntParam;
use Laminas\Cli\Input\ParamAwareInputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParamCommand extends Command
{
    use ParamAwareCommandTrait;

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

    /**
     * @param ParamAwareInputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $int = $input->getParam('int-param');
        $output->writeln('Int param value: ' . $int);

        return 0;
    }
}
