<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\CommandLoader\CommandLoaderInterface as SymfonyCommandLoaderInterface;

/**
 * This interface is just to refer to a command loader provided by the application which uses this component.
 * There is no need to implement this interface directly. Applications *MUST* implement the
 * {@see SymfonyCommandLoaderInterface Symfony CommandLoaderInterface}.
 */
interface CommandLoaderInterface extends SymfonyCommandLoaderInterface
{
}
