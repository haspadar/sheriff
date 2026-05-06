<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings;

use Haspadar\Sheriff\Settings\Value\StringValue;
use Haspadar\Sheriff\Settings\Value\Value;
use Override;

/**
 * Settings decorator that exposes derived keys read from `composer.json`.
 *
 * Currently provides `phpcs.root_namespace` — the first PSR-4 root namespace
 * declared by the project. Other composer-derived keys can be added here
 * without touching the rest of the Settings stack.
 *
 * Example:
 *
 *     new ComposerSettings(new DefaultSettings(), '/path/to/composer.json');
 */
final readonly class ComposerSettings implements Settings
{
    private const string ROOT_NAMESPACE_KEY = 'phpcs.root_namespace';

    /**
     * Initializes with the underlying settings and the composer.json path.
     *
     * @param Settings $base Underlying settings whose keys are passed through
     * @param string $composer Absolute path to the composer.json file
     */
    public function __construct(private Settings $base, private string $composer) {}

    #[Override]
    public function has(string $name): bool
    {
        return $name === self::ROOT_NAMESPACE_KEY || $this->base->has($name);
    }

    #[Override]
    public function value(string $name): Value
    {
        if ($name === self::ROOT_NAMESPACE_KEY && !$this->base->has($name)) {
            return new StringValue(
                (new ComposerRootNamespace($this->composer))->toString(),
            );
        }

        return $this->base->value($name);
    }

    #[Override]
    public function keys(): array
    {
        $baseKeys = $this->base->keys();

        return in_array(self::ROOT_NAMESPACE_KEY, $baseKeys, true)
            ? $baseKeys
            : [...$baseKeys, self::ROOT_NAMESPACE_KEY];
    }
}
