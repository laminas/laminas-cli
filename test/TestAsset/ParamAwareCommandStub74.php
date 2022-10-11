<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class ParamAwareCommandStub74 extends AbstractParamAwareCommandStub
{
    /**
     * @param string|array|null $shortcut
     * @param null|mixed        $default Defaults to null.
     * @return $this
     */
    public function addOption(
        string $name,
        $shortcut = null,
        ?int $mode = null,
        string $description = '',
        $default = null
    ): static {
        return $this->doAddOption($name, $shortcut, $mode, $description, $default);
    }
}
