<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Render\Json;

use Haspadar\Sheriff\Chain\Render\Json\JsonFloat;
use Haspadar\Sheriff\Settings\Value\FloatValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class JsonFloatTest extends TestCase
{
    #[Test]
    public function rendersFractionalPayloadVerbatim(): void
    {
        self::assertSame(
            '0.5',
            (new JsonFloat(new FloatValue(0.5)))->rendered(),
            'JsonFloat must render fractional payloads as their decimal representation',
        );
    }

    #[Test]
    public function appendsDotZeroToWholeFloatToKeepItDistinctFromInteger(): void
    {
        self::assertSame(
            '80.0',
            (new JsonFloat(new FloatValue(80.0)))->rendered(),
            'JsonFloat must append `.0` so a whole-number float stays distinguishable from a json integer',
        );
    }

    #[Test]
    public function rejectsInfinityPayload(): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new JsonFloat(new FloatValue(INF)))->rendered();
    }

    #[Test]
    public function rejectsNanPayload(): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new JsonFloat(new FloatValue(NAN)))->rendered();
    }
}
