<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Config;

/**
 * Root namespace from the PSR-4 autoload section of composer.json.
 */
final readonly class ComposerRootNamespace
{
    /**
     * Initializes with the composer.json file path.
     *
     * @param string $path Absolute path to the composer.json file
     */
    public function __construct(private string $path) {}

    /** Returns the first PSR-4 root namespace as a string. */
    public function toString(): string
    {
        if (!is_file($this->path)) {
            return '';
        }

        $contents = @file_get_contents($this->path);
        /** @var array{autoload?: array{psr-4?: array<string, string>}} $data */
        $data = json_decode(is_string($contents) ? $contents : '{}', true) ?? [];
        $namespaces = $data['autoload']['psr-4'] ?? [];

        return $namespaces !== []
            ? rtrim(array_key_first($namespaces), '\\')
            : '';
    }
}
