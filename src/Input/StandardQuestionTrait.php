<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function array_map;
use function implode;
use function is_array;
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
    private function createQuestion(): Question
    {
        /** @var null|string|string[] $defaultValue */
        $defaultValue  = $this->getDefault();
        $defaultPrompt = $this->getDefaultPrompt($defaultValue);
        $multiPrompt   = sprintf(
            "\n(Multiple entries allowed; hit Return after each.%s Hit Return to stop prompting)\n",
            $this->isRequired() ? ' At least one entry is required.' : ''
        );

        return new Question(
            sprintf(
                '<question>%s:</question>%s%s%s > ',
                $this->getDescription(),
                $this->getOptionMode() & InputOption::VALUE_IS_ARRAY ? $multiPrompt : '',
                $defaultPrompt,
                PHP_EOL
            ),
            $defaultValue
        );
    }

    /**
     * @param null|string|string[] $defaultValue
     */
    private function getDefaultPrompt($defaultValue): string
    {
        if (null === $defaultValue) {
            return '';
        }

        if (is_array($defaultValue)) {
            Assert::allScalar($defaultValue, 'One or more default values were not presented as scalars');
            $defaultValue = implode(', ', array_map(
                /** @param bool|int|float|string $value */
                function ($value): string {
                    return (string) $value;
                },
                $defaultValue
            ));
        }

        return sprintf(' [<comment>%s</comment>]', $defaultValue);
    }

    /** @return mixed */
    abstract public function getDefault();

    abstract public function getDescription(): string;

    abstract public function getOptionMode(): int;

    abstract public function isRequired(): bool;
}
