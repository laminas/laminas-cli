<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'modules'                 => [],
    'module_listener_options' => [],
    'laminas-cli'             => [
        'commands' => [
            'example:command-with-deps' => ExampleCommandWithDependencies::class,
        ],
    ],
    'service_manager'         => [
        'factories' => [
            ExampleCommandWithDependencies::class => ExampleCommandWithDependenciesFactory::class,
            ExampleDependency::class              => InvokableFactory::class,
        ],
    ],
];
