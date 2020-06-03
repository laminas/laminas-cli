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

    /**
     * {@inheritDoc}
     *
     * @param string|array $values
     * @param bool         $onlyParams
     * @return bool
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|array $values
     * @param mixed        $default
     * @param bool         $onlyParams
     * @return mixed
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     * @return mixed
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * {@inheritDoc}
     *
     * @param string               $name
     * @param string|string[]|null $value The argument value
     */
    public function setArgument($name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * {@inheritDoc}
     *
     * @param string                    $name
     * @param string|string[]|bool|null $value The option value
     */
    public function setOption($name, $value)
    {
        $this->input->setOption($name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $interactive
     */
    public function setInteractive($interactive)
    {
        $this->input->setInteractive($interactive);
    }
}
