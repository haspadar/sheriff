<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonList;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonListTest extends TestCase
{
    #[Test]
    public function rendersEmptyListAsBracePair(): void
    {
        self::assertSame(
            '[]',
            (new JsonList(new ListValue([])))->rendered(),
            'JsonList must render an empty list as the json empty array literal',
        );
    }

    #[Test]
    public function rendersStringEntriesAsBlockArrayWithDefaultIndent(): void
    {
        self::assertSame(
            "[\n    \"a\",\n    \"b\"\n]",
            (new JsonList(new ListValue([
                new StringValue('a'),
                new StringValue('b'),
            ])))->rendered(),
            'JsonList must place each item on its own line, indented one level deeper than the opening bracket',
        );
    }

    #[Test]
    public function rendersScalarMixWithMatchingJsonRenderers(): void
    {
        self::assertSame(
            "[\n    true,\n    8,\n    \"x\"\n]",
            (new JsonList(new ListValue([
                new BoolValue(true),
                new IntValue(8),
                new StringValue('x'),
            ])))->rendered(),
            'JsonList must dispatch each item through its matching json renderer',
        );
    }

    #[Test]
    public function indentsMoreDeeplyAtHigherDepth(): void
    {
        self::assertSame(
            "[\n        \"a\"\n    ]",
            (new JsonList(new ListValue([new StringValue('a')]), 1))->rendered(),
            'JsonList must indent items by depth+1 levels and the closing bracket by depth levels',
        );
    }

    #[Test]
    public function passesIncrementedDepthToNestedList(): void
    {
        self::assertSame(
            "[\n    [\n        \"x\"\n    ]\n]",
            (new JsonList(new ListValue([
                new ListValue([new StringValue('x')]),
            ])))->rendered(),
            'JsonList must forward depth+1 to nested containers so inner items sit at depth+2 indentation',
        );
    }
}
