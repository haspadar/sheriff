<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use Haspadar\Sheriff\Files\TextFiles;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextFilesTest extends TestCase
{
    #[Test]
    public function exposesAllTextFiles(): void
    {
        self::assertThat(
            new TextFiles([
                'README.md' => 'Sheriff',
                'config/app.ini' => 'name=sheriff',
            ]),
            new HasFiles([
                'README.md' => 'Sheriff',
                'config/app.ini' => 'name=sheriff',
            ]),
            'TextFiles must expose all files provided as key-value pairs',
        );
    }
}
