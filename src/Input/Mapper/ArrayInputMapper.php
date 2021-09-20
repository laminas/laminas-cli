<?php

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

            /** @psalm-suppress MixedAssignment The return value of `InputInterface#getOption`
             *                  and `InputInterface#getArgument` is `mixed` and thus we have to assume it here as well.
             */
            $params[$new] = strpos($old, '-') === 0
                ? $input->getOption(ltrim($old, '-'))
                : $input->getArgument($old);
        }

        return $params;
    }
}
