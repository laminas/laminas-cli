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

class ParamAwareInput implements InputInterface, StreamableInputInterface
{
    /** @var QuestionHelper */
    private $helper;

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /**
     * @var array<string, InputParamInterface>
     */
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

        $value = $this->input->getOption($name);
        $inputParam = $this->params[$name];

        if (! $inputParam instanceof InputParamInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid parameter type; must be of type %s',
                InputParamInterface::class
            ));
        }

        $question = $inputParam->getQuestion();

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
     * {@inheritdoc}
     */
    public function getFirstArgument()
    {
        return $this->input->getFirstArgument();
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values, bool $onlyParams = false)
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false, bool $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $onlyParams);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(InputDefinition $definition)
    {
        $this->input->bind($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->input->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->input->getArguments();
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setArgument(string $name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->input->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(string $name, $value)
    {
        $this->input->setOption($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption(string $name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritdoc}
     */
    public function setInteractive(bool $interactive)
    {
        $this->input->setInteractive($interactive);
    }

    /**
     * {@inheritdoc}
     */
    public function setStream($stream)
    {
        if (! $this->input instanceof StreamableInputInterface) {
            return;
        }
        $this->input->setStream($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if (! $this->input instanceof StreamableInputInterface) {
            return null;
        }
        return $this->input->getStream();
    }
}
