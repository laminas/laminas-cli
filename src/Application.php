<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class Application extends SymfonyConsoleApplication
{
    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function doRun(InputInterface $input, OutputInterface $output) : int
    {
        $this->input = $input;
        $this->output = $output;

        return parent::doRun($input, $output);
    }

    /**
     * @internal
     */
    public function getInput() : InputInterface
    {
        return $this->input;
    }

    /**
     * @internal
     */
    public function getOutput() : OutputInterface
    {
        return $this->output;
    }
}
