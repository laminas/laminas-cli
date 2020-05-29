<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

use function gettype;
use function is_int;
use function is_numeric;
use function sprintf;

final class IntParam implements InputParamInterface
{
    use StandardQuestionTrait;

    /** @var null|int */
    private $max;

    /** @var null|int */
    private $min;

    public function __construct(string $name, ?int $min = null, ?int $max = null)
    {
        $this->name = $name;
        $this->min  = $min;
        $this->max  = $max;
    }

    public function getOptionMode(): ?int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getQuestion(): Question
    {
        $question = $this->createQuestion();

        $question->setNormalizer(static function ($value) {
            if (is_numeric($value) && (string) (int) $value === $value) {
                return (int) $value;
            }

            return $value;
        });

        $question->setValidator(function ($value) {
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
        });

        return $question;
    }
}
