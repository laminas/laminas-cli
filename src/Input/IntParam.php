<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function get_debug_type;
use function is_numeric;
use function sprintf;

final class IntParam extends AbstractInputParam
{
    use AllowMultipleTrait;
    use StandardQuestionTrait;

    /** @var null|int */
    private $max;

    /** @var null|int */
    private $min;

    public function getQuestion(): Question
    {
        $question = $this->createQuestion();

        $question->setNormalizer(
            /**
             * @psalm-template ValueType of mixed
             * @psalm-param callable(ValueType): bool $normalizer
             * @psalm-param ValueType $value
             * @psalm-return mixed
             */
            static function ($value) {
                if (is_numeric($value) && (string) (int) $value === $value) {
                    return (int) $value;
                }

                return $value;
            }
        );

        $question->setValidator(
            /**
             * @psalm-template ValueType of mixed
             * @psalm-param callable(ValueType): bool $validator
             * @psalm-param ValueType $value
             */
            function ($value): int {
                Assert::integer($value, sprintf('Invalid value: integer expected, %s given', get_debug_type($value)));

                if ($this->min !== null) {
                    Assert::greaterThanEq($value, $this->min, sprintf(
                        'Invalid value %d; minimum value is %d',
                        $value,
                        $this->min
                    ));
                }

                if ($this->max !== null) {
                    Assert::lessThanEq($value, $this->max, sprintf(
                        'Invalid value %d; maximum value is %d',
                        $value,
                        $this->max
                    ));
                }

                return $value;
            }
        );

        return $question;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;
        return $this;
    }
}
