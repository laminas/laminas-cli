<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use function sprintf;

final class ChoiceParam extends AbstractInputParam
{
    /** @var array */
    private $haystack;

    /**
     * @param array $haystack Choices to choose from.
     */
    public function __construct(string $name, array $haystack)
    {
        parent::__construct($name);
        $this->haystack = $haystack;
    }

    public function getQuestion(): Question
    {
        $defaultValue  = $this->getDefault();
        $defaultPrompt = $defaultValue !== null
            ? sprintf(' [<comment>%s</comment>]', $defaultValue)
            : '';
        return new ChoiceQuestion(
            sprintf(
                '<question>%s?</question>%s',
                $this->getDescription(),
                $defaultPrompt
            ),
            $this->haystack,
            $defaultValue
        );
    }
}
