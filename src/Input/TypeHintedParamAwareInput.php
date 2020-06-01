<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_array;
use function sprintf;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * Compatible with symfony/console 5.0+.
 *
 * @internal
 */
final class TypeHintedParamAwareInput implements ParamAwareInputInterface
{
    /** @var QuestionHelper */
    private $helper;

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var array array<string, InputParamInterface> */
    private $params;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper, array $params)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->params = $params;
    }

    /**
     * @return null|mixed
     * @throws InvalidArgumentException When the parameter does not exist.
     * @throws InvalidArgumentException When the parameter is of an invalid type.
     * @throws InvalidArgumentException When the parameter is required, input is
     *     non-interactive, and no value is provided.
     */
    public function getParam(string $name)
    {
        if (! is_array($this->params) || ! isset($this->params[$name])) {
            throw new InvalidArgumentException(sprintf('Invalid parameter name: %s', $name));
        }

        $value      = $this->input->getOption($name);
        $inputParam = $this->params[$name];

        if (! $inputParam instanceof InputParamInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid parameter type; must be of type %s',
                InputParamInterface::class
            ));
        }

        $question = $inputParam->getQuestion();

        // @todo Remove once https://github.com/symfony/symfony/issues/37046 is
        //     addressed
        if ($question->getMaxAttempts() === null) {
            $question->setMaxAttempts(1000);
        }

        if ($value === null && ! $this->input->isInteractive()) {
            $value = $inputParam->getDefault();
        }

        if ($value !== null) {
            $validator = $question->getValidator();
            if ($validator) {
                $validator($value);
            }

            $normalizer = $question->getNormalizer();

            return $normalizer === null ? $value : $normalizer($value);
        }

        if (! $this->input->isInteractive() && $inputParam->isRequired()) {
            throw new InvalidArgumentException(sprintf('Missing required value for --%s parameter', $name));
        }

        $value = $this->helper->ask($this, $this->output, $question);

        // set the option value so it can be reused in chains
        $this->input->setOption($name, $value);

        return $value;
    }

    /**
     * {@inheritDoc}
     *
     * @return null|string
     */
    public function getFirstArgument()
    {
        return $this->input->getFirstArgument();
    }

    /**
     * {@inheritDoc}
     *
     * @param string|array $values
     * @return bool
     */
    public function hasParameterOption($values, bool $onlyParams = false)
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|array $values
     * @param mixed        $default
     * @return null|mixed
     */
    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $onlyParams);
    }

    /**
     * {@inheritDoc}
     */
    public function bind(InputDefinition $definition)
    {
        $this->input->bind($definition);
    }

    /**
     * {@inheritDoc}
     */
    public function validate()
    {
        $this->input->validate();
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->input->getArguments();
    }

    /**
     * {@inheritDoc}
     *
     * @return null|mixed
     */
    public function getArgument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|string[]|null $value The argument value
     */
    public function setArgument(string $name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|int $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->input->getOptions();
    }

    /**
     * {@inheritDoc}
     *
     * @return null|mixed
     */
    public function getOption(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|string[]|bool|null $value The option value
     */
    public function setOption(string $name, $value)
    {
        $this->input->setOption($name, $value);
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function hasOption(string $name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritDoc}
     */
    public function setInteractive(bool $interactive)
    {
        $this->input->setInteractive($interactive);
    }

    /**
     * {@inheritDoc}
     *
     * @param null|resource $stream
     */
    public function setStream($stream)
    {
        if (! $this->input instanceof StreamableInputInterface) {
            return;
        }
        $this->input->setStream($stream);
    }

    /**
     * {@inheritDoc}
     *
     * @return null|resource
     */
    public function getStream()
    {
        if (! $this->input instanceof StreamableInputInterface) {
            return null;
        }
        return $this->input->getStream();
    }
}
