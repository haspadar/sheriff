<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonTree;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\TreeValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonTreeTest extends TestCase
{
    #[Test]
    public function rendersEmptyTreeAsBracePair(): void
    {
        self::assertSame(
            '{}',
            (new JsonTree(new TreeValue([])))->rendered(),
            'JsonTree must render an empty tree as the json empty object literal',
        );
    }

    #[Test]
    public function rendersScalarsAsBlockObjectWithDefaultIndent(): void
    {
        self::assertSame(
            "{\n    \"compact\": true,\n    \"timeout\": 30\n}",
            (new JsonTree(new TreeValue([
                'compact' => new BoolValue(true),
                'timeout' => new IntValue(30),
            ])))->rendered(),
            'JsonTree must place each entry on its own indented line, with comma between entries',
        );
    }

    #[Test]
    public function recursesIntoNestedTreesAndLists(): void
    {
        self::assertSame(
            "{\n    \"logs\": {\n        \"text\": \"infection.log\"\n    },\n    \"patterns\": [\n        \"a\"\n    ]\n}",
            (new JsonTree(new TreeValue([
                'logs' => new TreeValue([
                    'text' => new StringValue('infection.log'),
                ]),
                'patterns' => new ListValue([new StringValue('a')]),
            ])))->rendered(),
            'JsonTree must align nested objects and arrays using their own depth',
        );
    }
}
