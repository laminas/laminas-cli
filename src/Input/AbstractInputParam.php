<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

use function in_array;
use function sprintf;

/**
 * Provide the majority of methods needed to implement InputParamInterface.
 *
 * This trait provides definitions for all but the following methods of the
 * InputParamInterface:
 *
 * - getOptionMode()
 * - getQuestion()
 *
 * Additionally, it defines the `$name` property, allowing implementations to
 * set it in their constructors without needing to define the property
 * themselves.
 */
abstract class AbstractInputParam implements InputParamInterface
{
    /** @var int[] */
    private $allowedModes = [
        InputOption::VALUE_NONE,
        InputOption::VALUE_REQUIRED,
        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
    ];

    /** @var mixed */
    private $default;

    /** @var string */
    private $description = '';

    /**
     * Parameter name; must be set by class composing trait!
     *
     * @var string
     */
    private $name;

    /**
     * InputOption mode to use with this parameter.
     *
     * @var int
     */
    private $optionMode = InputOption::VALUE_REQUIRED;

    /** @var bool */
    private $required = false;

    /** @var null|string */
    private $shortcut;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Default value to use if none provided.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptionMode(): int
    {
        return $this->optionMode;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefault($defaultValue): InputParamInterface
    {
        $this->default = $defaultValue;
        return $this;
    }

    public function setDescription(string $description): InputParamInterface
    {
        $this->description = $description;
        return $this;
    }

    public function setOptionMode(int $mode): InputParamInterface
    {
        if (! in_array($mode, $this->allowedModes, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid option mode; must be one of %s::VALUE_NONE,'
                . ' VALUE_REQUIRED, or VALUE_REQUIRED | VALUE_IS_ARRAY',
                InputOption::class
            ));
        }
        $this->optionMode = $mode;
        return $this;
    }

    public function setShortcut(?string $shortcut): InputParamInterface
    {
        $this->shortcut = $shortcut;
        return $this;
    }

    public function setRequiredFlag(bool $required): InputParamInterface
    {
        $this->required = $required;
        return $this;
    }
}
