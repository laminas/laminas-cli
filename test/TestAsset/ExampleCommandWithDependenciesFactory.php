<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ExampleCommandWithDependenciesFactory
{
    public function __invoke(ContainerInterface $container): ExampleCommandWithDependencies
    {
        $dependency = $container->get(ExampleDependency::class);
        Assert::isInstanceOf($dependency, ExampleDependency::class);
        return new ExampleCommandWithDependencies($dependency);
    }
}
