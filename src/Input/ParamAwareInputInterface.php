<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;

interface ParamAwareInputInterface extends
    InputInterface,
    StreamableInputInterface
{
    /**
     * @return mixed
     */
    public function getParam(string $name);
}
