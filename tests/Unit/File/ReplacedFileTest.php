<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\File;

use Haspadar\Sheriff\File\ReplacedFile;
use Haspadar\Sheriff\File\TextFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReplacedFileTest extends TestCase
{
    #[Test]
    public function replacesContents(): void
    {
        self::assertSame(
            'version=1.2.3',
            (new ReplacedFile(
                new TextFile(
                    'config/app.ini',
                    'version={{version}}',
                ),
                '{{version}}',
                '1.2.3',
            ))->contents(),
            'ReplacedFile must substitute the placeholder in file contents',
        );
    }

    #[Test]
    public function delegatesName(): void
    {
        self::assertSame(
            'README.md',
            (new ReplacedFile(
                new TextFile(
                    'README.md',
                    'x',
                ),
                'x',
                'y',
            ))->name(),
            'ReplacedFile must delegate name() to the origin file',
        );
    }

    #[Test]
    public function preservesOriginMode(): void
    {
        $file = new ReplacedFile(
            new TextFile('file.txt', 'abc', 0o700),
            'a',
            'b',
        );

        self::assertSame(0o700, $file->mode(), 'ReplacedFile must preserve the origin file mode');
    }
}
