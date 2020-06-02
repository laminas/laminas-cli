<?php

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
     */
    public function setDefault($defaultValue): self;

    public function setDescription(string $description): self;

    public function setShortcut(string $shortcut): self;

    public function setRequiredFlag(bool $required): self;
}
