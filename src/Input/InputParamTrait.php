<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use ArrayObject;
use InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

trait InputParamTrait
{
    /**
     * @internal
     * @var null|ArrayObject<string, InputParam>
     */
    private $inputParams;

    /**
     * @param null|mixed $default
     * @return $this
     */
    final public function addParam(
        string $name,
        string $description,
        string $type,
        bool $required = false,
        $default = null,
        array $options = []
    ) : self {
        $mode = $type === InputParam::TYPE_BOOL
            ? InputOption::VALUE_NONE
            : InputOption::VALUE_REQUIRED;

        $this->addOption(
            $name,
            null,
            $mode,
            $description
            // default null, on purpose
        );

        if ($this->inputParams === null) {
            $this->inputParams = new ArrayObject();
        }

        $this->inputParams->offsetSet($name, new InputParam($name, $description, $type, $required, $default, $options));

        return $this;
    }

    /**
     * @return null|bool|int|string
     * @throws InvalidArgumentException
     */
    final public function getParam(string $name)
    {
        if ($this->inputParams === null || ! $this->inputParams->offsetExists($name)) {
            throw new InvalidArgumentException(sprintf('Invalid parameter name: %s', $name));
        }

        /** @var InputInterface $input */
        $input = $this->getApplication()->getInput();

        /** @var OutputInterface $output */
        $output = $this->getApplication()->getOutput();

        $value = $input->getOption($name);
        $inputParam = $this->inputParams->offsetGet($name);
        $question = $inputParam->getQuestion($name);

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
     * @return Application
     */
    abstract public function getApplication();
}
