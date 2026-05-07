<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Render\Neon\NeonBlockList;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use Haspadar\Sheriff\Settings\Value\IntValue;
use Haspadar\Sheriff\Settings\Value\ListValue;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeonBlockListTest extends TestCase
{
    #[Test]
    public function rendersEmptyListAsFlowSequence(): void
    {
        self::assertSame(
            '[]',
            (new NeonBlockList(new ListValue([])))->rendered(),
            'NeonBlockList must collapse an empty list to the neon empty-flow sequence',
        );
    }

    #[Test]
    public function rendersStringEntriesBareWithLeadingNewlineAndDepthIndent(): void
    {
        self::assertSame(
            "\n        - \\Throwable\n        - \\App\\My",
            (new NeonBlockList(
                new ListValue([
                    new StringValue('\\Throwable'),
                    new StringValue('\\App\\My'),
                ]),
                1,
            ))->rendered(),
            'NeonBlockList must emit one item per line, indented by depth+1, with bare strings when safe',
        );
    }

    #[Test]
    public function rendersScalarMixWithMatchingNeonRenderers(): void
    {
        self::assertSame(
            "\n    - true\n    - 8\n    - bare",
            (new NeonBlockList(new ListValue([
                new BoolValue(true),
                new IntValue(8),
                new StringValue('bare'),
            ])))->rendered(),
            'NeonBlockList must render each entry through its matching neon renderer',
        );
    }
}
