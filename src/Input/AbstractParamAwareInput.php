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
use Symfony\Component\Console\Question\Question;

use function is_array;
use function sprintf;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * @internal
 */
abstract class AbstractParamAwareInput implements ParamAwareInputInterface
{
    /** @var QuestionHelper */
    protected $helper;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    /** @var array array<string, InputParamInterface> */
    protected $params;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper, array $params)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->params = $params;
    }

    /**
     * Define this method in order to modify the question, if needed, before
     * prompting for an answer.
     */
    abstract protected function modifyQuestion(Question $question): void;

    /**
     * @return mixed
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

        $this->modifyQuestion($question);

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
     * @return bool
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
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
