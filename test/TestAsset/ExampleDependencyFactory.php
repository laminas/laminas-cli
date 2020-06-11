<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Psr\Container\ContainerInterface;

class ExampleDependencyFactory
{
    public function __invoke(ContainerInterface $container): ExampleDependency
    {
        return new ExampleDependency('default value');
    }
}
