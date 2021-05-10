<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * Compatible with symfony/console version 4 series.
 *
 * @internal
 *
 * @todo Remove when we drop support for symfony/console 4.
 */
final class NonHintedParamAwareInput extends AbstractParamAwareInput
{
    protected function modifyQuestion(Question $question): void
    {
        // deliberate no-op
    }

    // Proxy methods implementing interface
    // phpcs:disable WebimpressCodingStandard.Functions.Param.MissingSpecification, WebimpressCodingStandard.Functions.ReturnType.ReturnValue

    public function hasParameterOption($values, $onlyParams = false)
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    public function setArgument($name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    public function setOption($name, $value)
    {
        $this->input->setOption($name, $value);
    }

    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    public function setInteractive($interactive)
    {
        $this->input->setInteractive($interactive);
    }

    // phpcs:enable
}
