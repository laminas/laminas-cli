<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Laminas\Cli\ContainerOptionFactory;
use Webmozart\Assert\Assert;

use function array_shift;
use function count;
use function in_array;
use function strpos;
use function substr;

/**
 * @internal
 */
final class ContainerInput implements ContainerInputInterface
{
    /** @var string */
    private $path = '';

    public function __construct(?array $argv = null)
    {
        $args = $argv ?? $_SERVER['argv'] ?? [];
        Assert::isList($args);
        Assert::allString($args);
        $this->parse($args);
    }

    public function get(): string
    {
        return $this->path;
    }

    /**
     * @psalm-param list<string> $args
     */
    private function parse(array $args): void
    {
        while (null !== $token = array_shift($args)) {
            if ('--' === $token) {
                break;
            }

            if (0 !== strpos($token, '--')) {
                continue;
            }

            $name = substr($token, 2);

            $pos   = strpos($name, '=');
            $value = '';

            if ($pos !== false) {
                $value = substr($name, $pos + 1);
                $name  = substr($name, 0, $pos);
            }

            if ($name !== ContainerOptionFactory::CONTAINER_OPTION) {
                continue;
            }

            if ($value !== '') {
                $this->path = $value;
                break;
            }

            if (count($args) === 0) {
                break;
            }

            $next = array_shift($args);
            if ((isset($next[0]) && '-' !== $next[0]) || in_array($next, ['', null], true)) {
                $value = $next;
            }

            $this->path = $value;
            break;
        }
    }
}
