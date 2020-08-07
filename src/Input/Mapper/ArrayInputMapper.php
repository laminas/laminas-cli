<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input\Mapper;

use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

use function is_array;
use function ltrim;
use function strpos;

final class ArrayInputMapper implements InputMapperInterface
{
    /** @psalm-var array<string|int, string|array<string, string>> */
    private $map;

    /**
     * @psalm-param array<string|int, string|array<string, string>> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function __invoke(InputInterface $input): array
    {
        $params = [];
        foreach ($this->map as $old => $new) {
            if (is_array($new)) {
                $params += $new;
                continue;
            }

            Assert::string($old, 'Keys in input map configuration must be strings');

            $params[$new] = strpos($old, '-') === 0
                ? $input->getOption(ltrim($old, '-'))
                : $input->getArgument($old);
        }

        return $params;
    }
}
