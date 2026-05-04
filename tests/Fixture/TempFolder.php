<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fixture;

use Haspadar\Sheriff\SheriffException;

final readonly class TempFolder
{
    private string $path;

    public function __construct()
    {
        $base = sys_get_temp_dir();
        $allocated = tempnam($base, '');

        if ($allocated === false) {
            throw new SheriffException('Failed to allocate temporary directory name');
        }

        if (!unlink($allocated)) {
            throw new SheriffException(
                sprintf('Failed to remove temp file: "%s"', $allocated),
            );
        }

        if (!mkdir($allocated, 0o755) && !is_dir($allocated)) {
            throw new SheriffException(
                sprintf('Failed to create temp directory: "%s"', $allocated),
            );
        }

        $this->path = $allocated;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function withFile(string $relativePath, string $contents): self
    {
        $file = $this->path . '/' . $relativePath;
        $dir = dirname($file);

        if (!is_dir($dir) && !mkdir($dir, 0o755, true) && !is_dir($dir)) {
            throw new SheriffException(
                sprintf('Failed to create directory: "%s"', $dir),
            );
        }

        if (file_put_contents($file, $contents) === false) {
            throw new SheriffException(
                sprintf('Failed to create file: "%s"', $file),
            );
        }

        return $this;
    }

    public function close(): void
    {
        $this->removeDirectory($this->path);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            throw new SheriffException(
                sprintf('Failed to scan directory: "%s"', $dir),
            );
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
