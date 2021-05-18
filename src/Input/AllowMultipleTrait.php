<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;

trait AllowMultipleTrait
{
    /**
     * Update the option mode to dis/allow multiple values.
     *
     * When enabled, the parameter will allow passing multiple options on the
     * command line, or, if none are provided, prompt multiple times for them.
     */
    public function setAllowMultipleFlag(bool $flag): self
    {
        if ($flag) {
            $this->optionMode |= InputOption::VALUE_IS_ARRAY;
            return $this;
        }

        $this->optionMode ^= InputOption::VALUE_IS_ARRAY;
        return $this;
    }
}
