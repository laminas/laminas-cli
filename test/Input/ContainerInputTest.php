<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use Laminas\Cli\Input\ContainerInput;
use PHPUnit\Framework\TestCase;

final class ContainerInputTest extends TestCase
{
    /**
     * @dataProvider parsableContainerOptions
     */
    public function testParseOptions(array $input, string $parsed): void
    {
        $input = new ContainerInput($input);

        self::assertSame($parsed, $input->get());
    }

    public function parsableContainerOptions(): array
    {
        return [
            [
                ['laminas', '--container=bar'],
                'bar',
            ],
            [
                ['laminas', '--container', 'bar'],
                'bar',
            ],
            [
                ['laminas', '--container='],
                '',
            ],
            [
                ['laminas', '--container=', 'bar'],
                'bar',
            ],
        ];
    }
}
