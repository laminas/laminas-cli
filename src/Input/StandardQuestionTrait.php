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

/**
 * Provide a standard question prompt.
 *
 * This trait composes InputParamTrait, and adds one method, `createQuestion()`.
 * The method returns a symfony/console Question with a prompt in the format:
 *
 * <code>
 * <question>{description}</question> [<comment>{default}</comment>]:
 * >
 * </code>
 *
 * Where:
 *
 * - {description} is filled by the $description property
 * - {default} is filled by the $default value associated with the param
 * - the " [<comment>{default}</comment>]" string is omitted when the
 *   $default value is null
 *
 * Consumers composing this trait can use this method to generate the initial
 * Question instance, and then further configure it (e.g., to add a normalizer
 * or validator).
 */
trait StandardQuestionTrait
{
    use InputParamTrait;

    private function createQuestion(): Question
    {
        $default = $this->default !== null
            ? sprintf(' [<comment>%s</comment>]', $this->default)
            : '';

        return new Question(
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
