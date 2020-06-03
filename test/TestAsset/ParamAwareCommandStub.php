<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParamAwareCommandStub
{
    /** @var InputInterface */
    public $input;

    /** @var OutputInterface */
    public $output;

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = $output;
        return 0;
    }
}
