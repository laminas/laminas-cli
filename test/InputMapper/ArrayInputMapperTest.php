<?php

declare(strict_types=1);

namespace LaminasTest\Cli\InputMapper;

use Laminas\Cli\Input\Mapper\ArrayInputMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class ArrayInputMapperTest extends TestCase
{
    public function testMapArgumentsAndOptions(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['arg1', 'foo-arg-1'],
            ['arg2', 'bar-arg-2'],
        ]);
        $input->method('getOption')->willReturnMap([
            ['opt1', true],
            ['opt2', 'baz'],
        ]);

        $mapper = new ArrayInputMapper([
            'arg1'   => 'first-arg',
            'arg2'   => 'second-arg',
            '--opt1' => '--first-opt',
            '--opt2' => 'third-arg',
        ]);

        self::assertSame(
            [
                'first-arg'   => 'foo-arg-1',
                'second-arg'  => 'bar-arg-2',
                '--first-opt' => true,
                'third-arg'   => 'baz',
            ],
            $mapper($input)
        );
    }

    public function testAdditionalParameter(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('name')->willReturn('FooBar');
        $input->method('getOption')->with('mode')->willReturn('dev');

        $mapper = new ArrayInputMapper([
            ['arg1' => 'foo'],
            ['--opt1' => 'bar'],
            'name'   => 'module',
            '--mode' => '--mode',
        ]);

        self::assertSame(
            [
                'arg1'   => 'foo',
                '--opt1' => 'bar',
                'module' => 'FooBar',
                '--mode' => 'dev',
            ],
            $mapper($input)
        );
    }
}
