<?php

declare(strict_types=1);

namespace Laminas\Cli;

use PackageVersions\Versions;

use function class_alias;
use function str_replace;
use function strstr;

/** @psalm-suppress DeprecatedClass */
$version = strstr(Versions::getVersion('symfony/console'), '@', true) ?: '';

if (str_replace('v', '', $version) >= '5.0.0') {
    // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase
    class_alias(ContainerCommandLoaderTypeHint::class, ContainerCommandLoader::class);
} else {
    // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase
    class_alias(ContainerCommandLoaderNoTypeHint::class, ContainerCommandLoader::class);
}
