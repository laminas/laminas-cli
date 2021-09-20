<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * @internal
 */
final class ParamAwareInput extends AbstractParamAwareInput
{
    protected function modifyQuestion(Question $question): void
    {
        // deliberate no-op
    }

    /**
     * @param string|array $values
     */
    public function hasParameterOption($values, bool $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * @param string|array                     $values
     * @param string|bool|int|float|array|null $default
     * @return mixed
     */
    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    /**
     * @return mixed
     */
    public function getArgument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * @param mixed $value
     */
    public function setArgument(string $name, $value): void
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * @return mixed
     */
    public function getOption(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * @param mixed $value
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
}
