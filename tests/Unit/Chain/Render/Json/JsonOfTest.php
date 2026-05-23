<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonBool;
use Haspadar\Sheriff\Chain\Render\Json\JsonFloat;
use Haspadar\Sheriff\Chain\Render\Json\JsonInt;
use Haspadar\Sheriff\Chain\Render\Json\JsonList;
use Haspadar\Sheriff\Chain\Render\Json\JsonOf;
use Haspadar\Sheriff\Chain\Render\Json\JsonString;
use Haspadar\Sheriff\Chain\Render\Json\JsonTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use Haspadar\Sheriff\Settings\Value\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class JsonOfTest extends TestCase
{
    /** @return iterable<string, array{Value, class-string}> */
    public static function valueRendererPairs(): iterable
    {
        yield 'bool' => [new BoolValue(true), JsonBool::class];
        yield 'int' => [new IntValue(8), JsonInt::class];
        yield 'float' => [new FloatValue(0.5), JsonFloat::class];
        yield 'string' => [new StringValue('x'), JsonString::class];
        yield 'list' => [new ListValue([]), JsonList::class];
        yield 'tree' => [new TreeValue([]), JsonTree::class];
    }

    /** @param class-string $expected */
    #[Test]
    #[DataProvider('valueRendererPairs')]
    public function dispatchesEachValueSubtypeToTheMatchingRenderer(
        Value $value,
        string $expected,
    ): void {
        self::assertInstanceOf(
            $expected,
            (new JsonOf($value))->renderer(),
            sprintf('JsonOf must dispatch %s to %s', $value::class, $expected),
        );
    }

    #[Test]
    public function rejectsValueSubtypeWithoutMatchingRenderer(): void
    {
        $this->expectException(TypeError::class);

        $unknown = new class () implements Value {
        };

        (new JsonOf($unknown))->renderer();
    }

    #[Test]
    public function threadsDefaultDepthZeroIntoContainerRenderer(): void
    {
        self::assertSame(
            "[\n    \"x\"\n]",
            (new JsonOf(new ListValue([new StringValue('x')])))->renderer()->rendered(),
            'JsonOf must thread depth=0 by default so a top-level list indents items one level',
        );
    }
}
