<?php // phpcs:disable WebimpressCodingStandard.Functions.ReturnType

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

interface InputParamInterface
{
    /**
     * Default value to use if none provided.
     *
     * @return mixed
     */
    public function getDefault();

    public function getDescription(): string;

    public function getName(): string;

    /**
     * Return the InputOption VALUE_* type.
     */
    public function getOptionMode(): ?int;

    public function getShortcut(): ?string;

    public function getQuestion(): Question;

    public function isRequired(): bool;

    /**
     * @param mixed $defaultValue
     * @return $this
     */
    public function setDefault($defaultValue): InputParamInterface;

    /**
     * @return $this
     */
    public function setDescription(string $description): InputParamInterface;

    /**
     * @return $this
     */
    public function setShortcut(string $shortcut): InputParamInterface;

    /**
     * @return $this
     */
    public function setRequiredFlag(bool $required): InputParamInterface;
}
