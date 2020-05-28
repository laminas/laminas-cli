<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

use function sprintf;

use const PHP_EOL;

trait ValidatedQuestionTrait
{
    use InputParamMethodsTrait;

    private function createQuestion(): Question
    {
        $default = $this->default !== null
            ? sprintf(' [<comment>%s</comment>]', $this->default)
            : '';
        $question = new Question(
            sprintf(
                '<question>%s:</question>%s%s > ',
                $this->description,
                $default,
                PHP_EOL
            ),
            $this->default
        );
    }
}
