<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Map;

use Haspadar\Sheriff\Chain\Mapped;
use Haspadar\Sheriff\Chain\Op;
use Override;

/**
 * Wraps the source op into a template only when its output is non-empty.
 *
 * Acts as a guarded `Formatted`: if the source renders to "", the result
 * stays "" so that surrounding markup is suppressed; otherwise the source
 * output is substituted into the sprintf template.
 *
 * Example:
 *
 *     (new WhenNotEmpty(new StringText(new StringValue('')), 'wrap: %s'))->rendered();
 *     // ""
 *     (new WhenNotEmpty(new StringText(new StringValue('x')), 'wrap: %s'))->rendered();
 *     // "wrap: x"
 */
final readonly class WhenNotEmpty implements Mapped
{
    /**
     * Initializes with the source op and the sprintf template applied when non-empty.
     *
     * @param Op $origin Source op whose rendered output is gated by emptiness
     * @param string $template Sprintf template with one %s placeholder, applied only when origin is non-empty
     */
    public function __construct(private Op $origin, private string $template) {}

    #[Override]
    public function rendered(): string
    {
        $rendered = $this->origin->rendered();

        return $rendered === ''
            ? ''
            : sprintf($this->template, $rendered);
    }
}
