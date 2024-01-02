<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Closure;

class ParamAwareCommandStub80 extends AbstractParamAwareCommandStub
{
    public function addOption(
        string $name,
        string|array|null $shortcut = null,
        int|null $mode = null,
        string $description = '',
        mixed $default = null,
        array|Closure $suggestedValues = []
    ): static {
        return $this->doAddOption($name, $shortcut, $mode, $description, $default);
    }
}
