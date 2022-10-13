<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function array_map;
use function get_debug_type;
use function in_array;
use function is_dir;
use function preg_replace;
use function rtrim;
use function scandir;
use function sprintf;

final class PathParam extends AbstractInputParam
{
    use AllowMultipleTrait;
    use StandardQuestionTrait;

    public const TYPE_DIR  = 'dir';
    public const TYPE_FILE = 'file';

    /**
     * Whether or not the path provided must exist.
     */
    private bool $mustExist = false;

    /**
     * One of the TYPE_* constants
     */
    private string $type;

    /**
     * @param string $pathType One of the TYPE_* constants, indicating whether
     *     the path expected should be a directory or a file.
     */
    public function __construct(string $name, string $pathType)
    {
        parent::__construct($name);
        $this->setPathType($pathType);
    }

    public function getQuestion(): Question
    {
        $question = $this->createQuestion();

        $question->setAutocompleterCallback(static function (string $userInput): array {
            // Strip any characters from the last slash to the end of the string
            // to keep only the last directory and generate suggestions for it
            $inputPath = preg_replace('%(/|^)[^/]*$%', '$1', $userInput);
            $inputPath = $inputPath === '' ? '.' : $inputPath;
            $inputPath = rtrim($inputPath, '/\\') . '/';

            $foundFilesAndDirs = is_dir($inputPath) ? scandir($inputPath) : [];

            return array_map(static fn(string $dirOrFile): string => $inputPath . $dirOrFile, $foundFilesAndDirs);
        });

        $mustExist = $this->mustExist;
        $type      = $this->type;

        $question->setValidator(
            static function (mixed $value) use ($mustExist, $type): string {
                Assert::string($value, sprintf('Invalid value: string expected, %s given', get_debug_type($value)));

                if (! $mustExist) {
                    // No further checks needed
                    return $value;
                }

                Assert::fileExists($value, 'Path does not exist');

                if ($type === self::TYPE_DIR) {
                    Assert::directory($value, 'Path is not a valid directory');
                }

                return $value;
            }
        );

        return $question;
    }

    public function setPathMustExist(bool $flag): self
    {
        $this->mustExist = $flag;
        return $this;
    }

    private function setPathType(string $type): void
    {
        if (! in_array($type, [self::TYPE_DIR, self::TYPE_FILE], true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid type provided: expected "%s" or "%s"',
                self::TYPE_DIR,
                self::TYPE_FILE
            ));
        }

        $this->type = $type;
    }
}
