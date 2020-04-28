<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Event\ConsoleEvent;

interface CommandListenerInterface
{
    public function __invoke(ConsoleEvent $event) : int;
}
