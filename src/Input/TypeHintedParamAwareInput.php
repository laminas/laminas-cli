<?php

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
        // deliberate no-op
    }

    // Proxy methods implementing interface
    // phpcs:disable WebimpressCodingStandard.Functions.Param.MissingSpecification, WebimpressCodingStandard.Functions.ReturnType.ReturnValue

    public function hasParameterOption($values, bool $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * @param string|array $values
     * @param mixed        $default
     * @return mixed
     */
    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    /**
     * @return null|string|string[]
     */
    public function getArgument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * @param null|string|string[] $value
     */
    public function setArgument(string $name, $value): void
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * @return null|bool|string|string[]
     */
    public function getOption(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * @param null|bool|string|string[] $value
     */
    public function setOption(string $name, $value): void
    {
        $this->input->setOption($name, $value);
    }

    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    public function setInteractive(bool $interactive): void
    {
        $this->input->setInteractive($interactive);
    }

    // phpcs:enable
}
