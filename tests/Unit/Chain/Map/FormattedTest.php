<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Map;

use ArgumentCountError;
use Haspadar\Sheriff\Chain\Map\Formatted;
use Haspadar\Sheriff\Chain\Render\Neon\NeonBool;
use Haspadar\Sheriff\Settings\Value\BoolValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FormattedTest extends TestCase
{
    #[Test]
    public function substitutesRenderedOutputIntoTemplate(): void
    {
        self::assertSame(
            'value: true',
            (new Formatted(new NeonBool(new BoolValue(true)), 'value: %s'))->rendered(),
            'Formatted must substitute the source op output into the sprintf template',
        );
    }

    #[Test]
    public function preservesPercentSignsInsideRenderedOutput(): void
    {
        self::assertSame(
            'coverage: 90% true',
            (new Formatted(new NeonBool(new BoolValue(true)), 'coverage: 90%% %s'))->rendered(),
            'Formatted must escape literal percents and still substitute the source op output',
        );
    }

    #[Test]
    public function returnsTemplateUnchangedWhenItHasNoPlaceholder(): void
    {
        self::assertSame(
            'literal',
            (new Formatted(new NeonBool(new BoolValue(true)), 'literal'))->rendered(),
            'Formatted must return the template as-is when there is no %s placeholder to fill',
        );
    }

    #[Test]
    public function failsWhenTemplateExpectsMorePlaceholdersThanProvided(): void
    {
        $this->expectException(ArgumentCountError::class);

        (new Formatted(new NeonBool(new BoolValue(true)), '%s and %s'))->rendered();
    }

    #[Test]
    public function failsWhenTemplateContainsTrailingPercent(): void
    {
        $this->expectException(ArgumentCountError::class);

        (new Formatted(new NeonBool(new BoolValue(true)), 'value: %s%'))->rendered();
    }
}
