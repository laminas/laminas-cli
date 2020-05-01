<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\InputMapper;

use Symfony\Component\Console\Input\InputInterface;

use function is_array;
use function ltrim;
use function strpos;

final class ArrayInputMapper implements InputMapperInterface
{
    /** @var string[] */
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function __invoke(InputInterface $input) : array
    {
        $params = [];
        foreach ($this->map as $old => $new) {
            if (is_array($new)) {
                $params += $new;
                continue;
            }

            $params[$new] = strpos($old, '-') === 0
                ? $input->getOption(ltrim($old, '-'))
                : $input->getArgument($old);
        }

        return $params;
    }
}
