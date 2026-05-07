<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Render\Neon;

use Haspadar\Sheriff\Chain\Rendered;
use Haspadar\Sheriff\Settings\Value\StringValue;
use Override;

/**
 * Renders a StringValue as a neon bare literal when its content is safe to write unquoted.
 *
 * The neon spec lets identifiers, single-segment paths and a few common literals
 * survive without quotes. Anything with whitespace, control characters or neon
 * structural characters falls back to the quoted form via NeonString.
 *
 * Example:
 *
 *     (new NeonBareString(new StringValue('table')))->rendered(); // table
 *     (new NeonBareString(new StringValue('a b')))->rendered(); // "a b"
 *     (new NeonBareString(new StringValue('\\Throwable')))->rendered(); // \Throwable
 */
final readonly class NeonBareString implements Rendered
{
    /**
     * Initializes with the value to render.
     *
     * @param StringValue $value String payload to render as a bare literal when possible
     */
    public function __construct(private StringValue $value) {}

    #[Override]
    public function rendered(): string
    {
        return $this->bareSafe()
            ? $this->value->raw
            : (new NeonString($this->value))->rendered();
    }

    /** Determines whether the raw payload can be emitted without surrounding quotes. */
    private function bareSafe(): bool
    {
        $raw = $this->value->raw;

        return $raw !== ''
            && !in_array($raw, ['true', 'false', 'null', 'yes', 'no', 'on', 'off'], true)
            && preg_match('/[\s"\'`#:,\[\]{}()]/u', $raw) !== 1
            && preg_match('/[\x00-\x1F\x7F]/', $raw) !== 1
            && !in_array($raw[0], ['-', '?', '@', '%', '!', '&', '*', '|', '>', '<', '='], true)
            && !is_numeric($raw);
    }
}
