<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

use function array_walk;
use function get_debug_type;
use function is_string;
use function sprintf;
use function trim;

/**
 * Provide the majority of methods needed to implement InputParamInterface.
 *
 * This class provides definitions for all but the following methods of the
 * InputParamInterface:
 *
 * - getQuestion()
 *
 * Implementations MUST call `parent::__construct()` with the name if overriding
 * the constructor.
 *
 * If an option mode other than InputOption::VALUE_REQUIRED is desired,
 * implementations should set the value themselves. NOTE: compose the
 * AllowMultipleTrait and use its `setAllowMultipleFlag()` if multiple values
 * can be accepted, but are not required.
 */
abstract class AbstractInputParam implements InputParamInterface
{
    /**
     * InputOption mode to use with this parameter.
     *
     * Protected, so that extending classes can change the value (e.g., via the
     * AllowMultipleTrait).
     *
     * @var int
     */
    protected $optionMode = InputOption::VALUE_REQUIRED;

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

    /**
     * @return null|string|string[]
     */
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
     * @psalm-suppress LessSpecificImplementedReturnType
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

    /**
     * @param null|string|string[] $shortcut
     * @psalm-suppress LessSpecificImplementedReturnType
     */
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

        if (is_string($shortcut)) {
            $trimmedShortcut = trim($shortcut, ' -');
            Assert::stringNotEmpty($trimmedShortcut, sprintf(
                'Shortcut must be null, a non-zero-length string, or an array of strings; received "%s"',
                get_debug_type($shortcut)
            ));
            return;
        }

        Assert::isNonEmptyList(
            $shortcut,
            sprintf(
                'Shortcut must be null, a non-zero-length string, or an array of strings; received "%s"',
                get_debug_type($shortcut)
            )
        );

        array_walk(
            $shortcut,
            /** @param mixed $shortcut */
            static function ($shortcut) {
                Assert::stringNotEmpty($shortcut, sprintf(
                    'Only non-empty strings are allowed as shortcut names; received "%s"',
                    get_debug_type($shortcut)
                ));

                if ('' === trim($shortcut, ' -')) {
                    throw new InvalidArgumentException(
                        'String values in arrays provided as shortcut names must not be empty'
                    );
                }
            }
        );
    }
}
