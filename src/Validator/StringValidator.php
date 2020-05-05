<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Validator;

use RuntimeException;

use function gettype;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @internal
 */
final class StringValidator
{
    /** @var null|string */
    private $pattern;

    /** @var bool */
    private $required = false;

    public function __construct(array $options = [])
    {
        if (isset($options['pattern'])) {
            $this->setPattern($options['pattern']);
        }

        if (isset($options['required'])) {
            $this->setRequired($options['required']);
        }
    }

    public function setPattern(string $pattern) : void
    {
        $this->pattern = $pattern;
    }

    public function setRequired(bool $required) : void
    {
        $this->required = $required;
    }

    /**
     * @param mixed $value
     * @return null|string Validated value.
     * @throws RuntimeException
     */
    public function __invoke($value)
    {
        if ($value === null && ! $this->required) {
            return null;
        }

        if (! is_string($value)) {
            throw new RuntimeException(sprintf('Invalid value: string expected, %s given', gettype($value)));
        }

        if ($this->pattern !== null && ! preg_match($this->pattern, $value)) {
            throw new RuntimeException('Invalid value: does not match pattern: ' . $this->pattern);
        }

        return $value;
    }
}
