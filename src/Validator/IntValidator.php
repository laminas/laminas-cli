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
use function is_int;
use function sprintf;

/**
 * @internal
 */
final class IntValidator
{
    /** @var null|int */
    private $min;

    /** @var null|int */
    private $max;

    /** @var bool */
    private $required = false;

    public function __construct(array $options = [])
    {
        if (isset($options['min'])) {
            $this->setMin($options['min']);
        }

        if (isset($options['max'])) {
            $this->setMax($options['max']);
        }

        if (isset($options['required'])) {
            $this->setRequired($options['required']);
        }
    }

    public function setMin(int $min) : void
    {
        $this->min = $min;
    }

    public function setMax(int $max) : void
    {
        $this->max = $max;
    }

    public function setRequired(bool $required) : void
    {
        $this->required = $required;
    }

    /**
     * @param mixed $value
     * @return null|int Validated value.
     * @throws RuntimeException
     */
    public function __invoke($value)
    {
        if ($value === null && ! $this->required) {
            return null;
        }

        if (! is_int($value)) {
            throw new RuntimeException(sprintf('Invalid value: integer expected, %s given', gettype($value)));
        }

        if ($this->min !== null && $value < $this->min) {
            throw new RuntimeException(sprintf('Invalid value %d; minimum value is %d', $value, $this->min));
        }

        if ($this->max !== null && $value > $this->max) {
            throw new RuntimeException(sprintf('Invalid value %d; maximum value is %d', $value, $this->max));
        }

        return $value;
    }
}
