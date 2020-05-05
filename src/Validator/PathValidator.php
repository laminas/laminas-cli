<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Validator;

use InvalidArgumentException;
use RuntimeException;

use function file_exists;
use function gettype;
use function in_array;
use function is_dir;
use function is_string;
use function sprintf;

/**
 * @internal
 */
final class PathValidator
{
    public const TYPE_DIR = 'dir';
    public const TYPE_FILE = 'file';

    /** @var null|string */
    private $type;

    /** @var bool */
    private $existing = false;

    /** @var string */
    private $base;

    /** @var bool */
    private $required = false;

    public function __construct(array $options = [])
    {
        if (isset($options['type'])) {
            $this->setType($options['type']);
        }

        if (isset($options['existing'])) {
            $this->setExisting($options['existing']);
        }

        // @todo decide if we need it
        // if (isset($options['base'])) {
        //     $this->setBase($options['base']);
        // }

        if (isset($options['required'])) {
            $this->setRequired($options['required']);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setType(string $type) : void
    {
        if (! in_array($type, [self::TYPE_DIR, self::TYPE_FILE], true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid type provided: expected "%s" or "%s"',
                self::TYPE_DIR,
                self::TYPE_FILE
            ));
        }
    }

    public function setExisting(bool $existing) : void
    {
        $this->existing = $existing;
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

        if ($this->existing) {
            if (! file_exists($value)) {
                throw new RuntimeException('Path does not exist');
            }

            if ($this->type === self::TYPE_DIR && ! is_dir($value)) {
                throw new RuntimeException('Path is not a valid directory');
            }
        }

        return $value;
    }
}
