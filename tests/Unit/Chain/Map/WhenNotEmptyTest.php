<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Chain\Map;

use Haspadar\Sheriff\Chain\Map\WhenNotEmpty;
use Haspadar\Sheriff\Chain\Plain\StringText;
use Haspadar\Sheriff\Settings\Value\StringValue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WhenNotEmptyTest extends TestCase
{
    #[Test]
    public function suppressesTemplateWhenSourceIsEmpty(): void
    {
        self::assertSame(
            '',
            (new WhenNotEmpty(new StringText(new StringValue('')), 'wrap: %s'))->rendered(),
            'WhenNotEmpty must drop the surrounding template when the source renders empty',
        );
    }

    #[Test]
    public function appliesTemplateWhenSourceIsPresent(): void
    {
        self::assertSame(
            'wrap: x',
            (new WhenNotEmpty(new StringText(new StringValue('x')), 'wrap: %s'))->rendered(),
            'WhenNotEmpty must apply the sprintf template when the source has content',
        );
    }
}
