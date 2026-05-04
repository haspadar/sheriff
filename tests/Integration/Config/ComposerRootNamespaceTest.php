<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Integration\Config;

use Haspadar\Sheriff\Config\ComposerRootNamespace;
use Haspadar\Sheriff\Tests\Fixture\TempFolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ComposerRootNamespaceTest extends TestCase
{
    #[Test]
    public function returnsFirstPsr4NamespaceFromComposerJson(): void
    {
        $folder = (new TempFolder())->withFile('composer.json', json_encode([
            'autoload' => ['psr-4' => ['Acme\\App\\' => 'src/']],
        ]) ?: '');

        self::assertSame(
            'Acme\\App',
            (new ComposerRootNamespace($folder->path() . '/composer.json'))->toString(),
            'ComposerRootNamespace must return the first PSR-4 namespace without trailing backslash',
        );

        $folder->close();
    }

    #[Test]
    public function returnsEmptyStringWhenComposerJsonIsMissing(): void
    {
        $folder = new TempFolder();

        self::assertSame(
            '',
            (new ComposerRootNamespace($folder->path() . '/composer.json'))->toString(),
            'ComposerRootNamespace must return empty string when composer.json does not exist',
        );

        $folder->close();
    }

    #[Test]
    public function returnsEmptyStringWhenPsr4SectionIsAbsent(): void
    {
        $folder = (new TempFolder())->withFile('composer.json', json_encode([
            'name' => 'acme/app',
        ]) ?: '');

        self::assertSame(
            '',
            (new ComposerRootNamespace($folder->path() . '/composer.json'))->toString(),
            'ComposerRootNamespace must return empty string when autoload.psr-4 is absent',
        );

        $folder->close();
    }

    #[Test]
    public function returnsFirstWhenMultiplePsr4NamespacesExist(): void
    {
        $folder = (new TempFolder())->withFile('composer.json', json_encode([
            'autoload' => ['psr-4' => [
                'Acme\\App\\' => 'src/',
                'Acme\\Tests\\' => 'tests/',
            ]],
        ]) ?: '');

        self::assertSame(
            'Acme\\App',
            (new ComposerRootNamespace($folder->path() . '/composer.json'))->toString(),
            'ComposerRootNamespace must return the first PSR-4 namespace when multiple entries exist',
        );

        $folder->close();
    }

    #[Test]
    public function returnsEmptyStringWhenComposerJsonIsNotReadable(): void
    {
        $folder = (new TempFolder())->withFile('composer.json', '{}');
        $path = $folder->path() . '/composer.json';
        chmod($path, 0o000);

        self::assertSame(
            '',
            (new ComposerRootNamespace($path))->toString(),
            'ComposerRootNamespace must return empty string when composer.json is not readable',
        );

        chmod($path, 0o644);
        $folder->close();
    }
}
