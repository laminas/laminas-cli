<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Application;
use RuntimeException;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

trait InputParamTrait
{
    /**
     * @internal
     * @var array<string, InputParamInterface>
     */
    private $inputParams = [];

    /**
     * @param null|mixed $default
     * @return $this
     * @throws RuntimeException
     */
    final public function addParam(InputParamInterface $param): self
    {
        if (! is_array($this->inputParams)) {
            throw new RuntimeException(sprintf(
                'Command %s uses $inputParams property. It is not allowed while using %s',
                static::class,
                InputParamTrait::class
            ));
        }

        $name = $param->getName();

        $this->addOption(
            $name,
            $param->getShortcut(),
            $param->getOptionMode(),
            $param->getDescription()
            // default null, on purpose
        );

        $this->inputParams[$name] = $param;

        return $this;
    }

    /**
     * @return null|bool|int|string
     * @throws InvalidArgumentException When the parameter does not exist.
     * @throws InvalidArgumentException When the parameter is of an invalid type.
     * @throws InvalidArgumentException When the parameter is required, input is
     *     non-interactive, and no value is provided.
     */
    final public function getParam(string $name)
    {
        if (! is_array($this->inputParams) || ! isset($this->inputParams[$name])) {
            throw new InvalidArgumentException(sprintf('Invalid parameter name: %s', $name));
        }

        $application = $this->getApplication();
        if (! $application instanceof Application) {
            throw new InvalidArgumentException(sprintf(
                'Input parameters only work when using %s (currently using %s)',
                Application::class,
                is_object($application) ? get_class($application) : gettype($application)
            ));
        }

        /** @var Application $application */
        /** @var InputInterface $input */
        $input = $application->getInput();

        /** @var OutputInterface $output */
        $output = $application->getOutput();

        $value = $input->getOption($name);
        $inputParam = $this->inputParams[$name];

        if (! $inputParam instanceof InputParamInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid parameter type; must be of type %s',
                InputParamInterface::class
            ));
        }

        $question = $inputParam->getQuestion();

        if ($value === null && ! $input->isInteractive()) {
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

        if (! $input->isInteractive() && $inputParam->isRequired()) {
            throw new InvalidArgumentException(sprintf('Missing required value for --%s parameter', $name));
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');
        $value = $helper->ask($input, $output, $question);

        // set the option value so it can be reused in chains
        $input->setOption($name, $value);

        return $value;
    }

    /**
     * @param string|array|null $shortcut
     * @param null|mixed $default
     * @return $this
     */
    abstract public function addOption(
        string $name,
        $shortcut = null,
        ?int $mode = null,
        string $description = '',
        $default = null
    );

    /**
     * @return SymfonyConsoleApplication
     */
    abstract public function getApplication();
}
