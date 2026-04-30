<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Storage;

use Haspadar\Piqule\File\File;
use Haspadar\Piqule\PiquleException;
use Override;

/**
 * Immutable in-memory storage backed by a location-to-File map.
 */
final readonly class InMemoryStorage implements Storage
{
    /**
     * Initializes with pre-loaded file entries.
     *
     * @param array<string, File> $entries Location-to-File map.
     */
    public function __construct(private array $entries = []) {}

    #[Override]
    public function read(string $location): string
    {
        if (!array_key_exists($location, $this->entries)) {
            throw new PiquleException("Location not found: {$location}");
        }

        return $this->entries[$location]->contents();
    }

    #[Override]
    public function mode(string $location): int
    {
        if (!array_key_exists($location, $this->entries)) {
            throw new PiquleException("Location not found: {$location}");
        }

        return $this->entries[$location]->mode();
    }

    #[Override]
    public function exists(string $location): bool
    {
        return array_key_exists($location, $this->entries);
    }

    #[Override]
    public function write(File $file): self
    {
        return new self([
            ...$this->entries,
            $file->name() => $file,
        ]);
    }

    #[Override]
    public function entries(string $location): iterable
    {
        $keys = array_keys($this->entries);

        if ($location === '') {
            return $keys;
        }

        $prefix = sprintf('%s/', rtrim($location, '/'));
        $matches = [];

        foreach ($keys as $key) {
            if (!str_starts_with($key, $prefix)) {
                continue;
            }

            $rest = substr($key, strlen($prefix));

            if ($rest !== '' && !str_contains($rest, '/')) {
                $matches[] = $key;
            }
        }

        return $matches;
    }
}
