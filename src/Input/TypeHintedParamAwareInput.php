<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * Compatible with symfony/console 5.0+.
 *
 * @internal
 */
final class TypeHintedParamAwareInput extends AbstractParamAwareInput
{
    protected function modifyQuestion(Question $question): void
    {
        // @todo Remove once https://github.com/symfony/symfony/issues/37046 is
        //     addressed
        if ($question->getMaxAttempts() === null) {
            $question->setMaxAttempts(1000);
        }
    }

    // Proxy methods implementing interface
    // phpcs:disable WebimpressCodingStandard.Functions.Param.MissingSpecification, WebimpressCodingStandard.Functions.ReturnType.ReturnValue

    public function hasParameterOption($values, bool $onlyParams = false)
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    public function getArgument(string $name)
    {
        return $this->input->getArgument($name);
    }

    public function setArgument(string $name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    public function getOption(string $name)
    {
        return $this->input->getOption($name);
    }

    public function setOption(string $name, $value)
    {
        $this->input->setOption($name, $value);
    }

    public function hasOption(string $name)
    {
        return $this->input->hasOption($name);
    }

    public function setInteractive(bool $interactive)
    {
        $this->input->setInteractive($interactive);
    }

    // phpcs:enable
}
