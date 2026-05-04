<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\File;

use Haspadar\Sheriff\File\TextFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextFileTest extends TestCase
{
    #[Test]
    public function exposesProvidedName(): void
    {
        self::assertSame(
            'path/to/file.txt',
            (new TextFile(
                'path/to/file.txt',
                'contents',
            ))->name(),
            'File must expose provided relative path',
        );
    }

    #[Test]
    public function exposesProvidedContents(): void
    {
        self::assertSame(
            'hello world',
            (new TextFile(
                'file.txt',
                'hello world',
            ))->contents(),
            'File must expose provided contents',
        );
    }

    #[Test]
    public function usesDefaultModeWhenNotProvided(): void
    {
        self::assertSame(
            0o644,
            (new TextFile(
                'file.txt',
                'data',
            ))->mode(),
            'Default mode must be 0644',
        );
    }

    #[Test]
    public function exposesProvidedMode(): void
    {
        self::assertSame(
            0o755,
            (new TextFile(
                'script.sh',
                '#!/bin/sh',
                0o755,
            ))->mode(),
            'File must expose provided mode',
        );
    }
}
