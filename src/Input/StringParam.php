<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Question\Question;

use function get_debug_type;
use function is_string;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function strstr;

use const E_WARNING;

final class StringParam extends AbstractInputParam
{
    use AllowMultipleTrait;
    use StandardQuestionTrait;

    /** @var null|string */
    private $pattern;

    public function getQuestion(): Question
    {
        $question = $this->createQuestion();

        $question->setValidator(
            /**
             * @param mixed $value
             */
            function ($value): string {
                if (! is_string($value)) {
                    throw new RuntimeException(sprintf(
                        'Invalid value: string expected, %s given',
                        get_debug_type($value)
                    ));
                }

                if ($this->pattern !== null && ! preg_match($this->pattern, $value)) {
                    throw new RuntimeException(sprintf(
                        'Invalid value: does not match pattern: %s',
                        $this->pattern
                    ));
                }

                return $value;
            }
        );

        return $question;
    }

    /**
     * @throws InvalidArgumentException If PCRE pattern is invalid.
     */
    public function setPattern(string $pattern): self
    {
        if (! $this->validatePattern($pattern)) {
            throw new InvalidArgumentException(sprintf('Invalid PCRE pattern "%s"', $pattern));
        }
        $this->pattern = $pattern;
        return $this;
    }

    private function validatePattern(string $pattern): bool
    {
        set_error_handler(static function (int $errno, string $errstr) {
            if (! strstr($errstr, 'preg_match')) {
                return false;
            }
        }, E_WARNING);

        $result = preg_match($pattern, '');

        restore_error_handler();

        return $result === false ? false : true;
    }
}
