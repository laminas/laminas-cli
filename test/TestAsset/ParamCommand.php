<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\Cli\Input\InputParam;
use Laminas\Cli\Input\InputParamTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParamCommand extends Command
{
    use InputParamTrait;

    protected function configure(): void
    {
        $this->addParam(
            'int-param',
            'Param description',
            InputParam::TYPE_INT,
            true,
            null,
            [
                'min' => 1,
                'max' => 10,
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $int = $this->getParam('int-param');
        $output->writeln('Int param value: ' . $int);

        return 0;
    }
}
