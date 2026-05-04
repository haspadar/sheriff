<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Formula;

use Override;

/**
 * Normalizes whitespace around pipe separators in a DSL expression.
 */
final readonly class NormalizedFormula implements Formula
{
    /**
     * Initializes with a raw DSL expression string.
     *
     * @param string $expression Raw DSL expression whose pipe whitespace will be normalized
     */
    public function __construct(private string $expression) {}

    #[Override]
    public function result(): string
    {
        $filtered = preg_replace('/\s*\|\s*/', '|', $this->expression) ?? $this->expression;

        return trim($filtered);
    }
}
