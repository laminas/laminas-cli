<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use function sprintf;

final class ChoiceParam implements InputParamInterface
{
    use InputParamTrait;

    /** @var array */
    private $haystack;

    /**
     * @param array $haystack Choices to choose from.
     */
    public function __construct(string $name, array $haystack)
    {
        $this->name     = $name;
        $this->haystack = $haystack;
    }

    public function getOptionMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getQuestion(): Question
    {
        $default = $this->default !== null
            ? sprintf(' [<comment>%s</comment>]', $this->default)
            : '';
        return new ChoiceQuestion(
            sprintf(
                '<question>%s?</question>%s',
                $this->description,
                $default
            ),
            $this->haystack,
            $this->default
        );
    }
}
