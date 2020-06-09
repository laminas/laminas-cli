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

use function array_walk;
use function in_array;
use function is_array;
use function is_string;
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

    /** @var null|string|string[] */
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

    // phpcs:ignore WebimpressCodingStandard.Functions.ReturnType.ReturnValue
    public function getShortcut()
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

    // phpcs:ignore WebimpressCodingStandard.Functions.Param.MissingSpecification
    public function setShortcut($shortcut): InputParamInterface
    {
        $this->validateShortcut($shortcut);
        $this->shortcut = $shortcut;
        return $this;
    }

    public function setRequiredFlag(bool $required): InputParamInterface
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param mixed $shortcut
     * @throws InvalidArgumentException When shortcut is an invalid type.
     * @throws InvalidArgumentException When shortcut is empty.
     */
    private function validateShortcut($shortcut): void
    {
        if (null === $shortcut) {
            return;
        }

        if (! is_array($shortcut) && ! is_string($shortcut)) {
            throw new InvalidArgumentException(sprintf(
                'Shortcut must be null, a string, or an array; received "%s"',
                get_debug_type($shortcut)
            ));
        }

        if (empty($shortcut)) {
            throw new InvalidArgumentException(sprintf(
                'Shortcut must be a non-zero-length string or an array of strings; received "%s"',
                is_string($shortcut) ? '' : '[]'
            ));
        }

        if (is_string($shortcut)) {
            return;
        }

        array_walk($shortcut, function ($shortcut) {
            if (null === $shortcut) {
                throw new InvalidArgumentException(
                    'No null values are allowed in arrays provided as shortcut names'
                );
            }

            if (! is_string($shortcut)) {
                throw new InvalidArgumentException(sprintf(
                    'Only string values are allowed in arrays provided as shortcut names; received "%s"',
                    get_debug_type($shortcut)
                ));
            }

            if (empty($shortcut)) {
                throw new InvalidArgumentException(
                    'String values in arrays provided as shortcut names must not be empty'
                );
            }
        });
    }
}
