<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Command;

use Laminas\Cli\Input\InputParamInterface;
use Laminas\Cli\Input\ParamAwareInput;
use RuntimeException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_array;
use function sprintf;

/**
 * Provide promptable input options to your command.
 *
 * Compose this trait in your symfony/console command in order to provide the
 * ability to define options that will result in interactive prompts when not
 * provided. Such parameters can be added using the `addParam()` construct. When
 * present, you can then use `$input->getParam($name)` to retrieve the value. If
 * the value was provided as an option, that value will be returned; otherwise,
 * it will prompt the user for the value.
 */
trait InputParamTrait
{
    /** @var array array<string, InputParamInterface> */
    private $inputParams = [];

    /**
     * @return $this
     * @throws RuntimeException
     */
    final public function addParam(InputParamInterface $param): self
    {
        if (! is_array($this->inputParams)) {
            throw new RuntimeException(sprintf(
                'Command %s uses $inputParams property; please do not override that property when using %s,'
                . ' as it becomes incompatible with input parameter usage',
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
     * @return int
     */
    final public function run(InputInterface $input, OutputInterface $output)
    {
        return parent::run(
            new ParamAwareInput(
                $input,
                $output,
                $this->getHelperSet()->get('question'),
                $this->inputParams
            ),
            $output
        );
    }

    /**
     * @param string|array|null $shortcut
     * @param null|mixed        $default
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
     * @return HelperSet
     */
    abstract public function getHelperSet();
}
