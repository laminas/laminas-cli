<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use PackageVersions\Versions;

use function class_alias;
use function str_replace;
use function strstr;

$version = strstr(Versions::getVersion('symfony/console'), '@', true);

if (str_replace('v', '', $version) >= '5.0.0') {
    class_alias(ContainerCommandLoaderTypeHint::class, ContainerCommandLoader::class);
} else {
    class_alias(ContainerCommandLoaderNoTypeHint::class, ContainerCommandLoader::class);
}
