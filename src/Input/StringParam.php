<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function get_debug_type;
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

    private ?string $pattern = null;

    public function getQuestion(): Question
    {
        $question = $this->createQuestion();
        $pattern  = $this->pattern;

        $question->setValidator(
            static function (mixed $value) use ($pattern): string {
                Assert::string($value, sprintf(
                    'Invalid value: string expected, %s given',
                    get_debug_type($value)
                ));

                if ($pattern !== null) {
                    Assert::regex($value, $pattern, sprintf(
                        'Invalid value: does not match pattern: %s',
                        $pattern
                    ));
                }

                return $value;
            }
        );

        return $question;
    }

    /**
     * @param non-empty-string $pattern
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

    /**
     * @param non-empty-string $pattern
     */
    private function validatePattern(string $pattern): bool
    {
        // phpcs:ignore WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCaps
        set_error_handler(static function (int $_, string $errstr): bool {
            if (! strstr($errstr, 'preg_match')) {
                return false;
            }

            return true;
        }, E_WARNING);
        // phpcs:enable WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCaps

        $result = preg_match($pattern, '');

        restore_error_handler();

        return $result === false ? false : true;
    }
}
