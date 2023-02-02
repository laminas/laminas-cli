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

    private ?int $max = null;

    private ?int $min = null;

    public function getQuestion(): Question
    {
        $question = $this->createQuestion();

        $question->setNormalizer(
        /**
         * @return mixed
         */
            static function (mixed $value) {
                if (is_numeric($value) && (string) (int) $value === $value) {
                    return (int) $value;
                }

                return $value;
            }
        );

        $min = $this->min;
        $max = $this->max;
        $question->setValidator(
            static function (mixed $value) use ($min, $max): int {
                Assert::integer($value, sprintf('Invalid value: integer expected, %s given', get_debug_type($value)));

                if ($min !== null) {
                    Assert::greaterThanEq($value, $min, sprintf(
                        'Invalid value %d; minimum value is %d',
                        $value,
                        $min
                    ));
                }

                if ($max !== null) {
                    Assert::lessThanEq($value, $max, sprintf(
                        'Invalid value %d; maximum value is %d',
                        $value,
                        $max
                    ));
                }

                return $value;
            }
        );

        return $question;
    }

    /** @return $this */
    public function setMin(?int $min): self
    {
        $this->min = $min;
        return $this;
    }

    /** @return $this */
    public function setMax(?int $max): self
    {
        $this->max = $max;
        return $this;
    }
}
