<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Map;

use Haspadar\Sheriff\Chain\Mapped;
use Haspadar\Sheriff\Chain\Op;
use Override;

/**
 * Wraps an Op's rendered output into a sprintf template.
 *
 * The template is fed straight to PHP's sprintf and may carry zero or one %s
 * placeholder. A template without %s is returned unchanged, with one %s the
 * origin's rendered output is inserted. Multiple %s or a trailing % is a
 * misuse: sprintf raises ArgumentCountError or ValueError at render time.
 *
 * Example:
 *
 *     (new Formatted(new NeonBool(new BoolValue(true)), 'value: %s'))->rendered();
 *     // "value: true"
 */
final readonly class Formatted implements Mapped
{
    /**
     * Initializes with the source op and a sprintf template.
     *
     * @param Op $origin Source op whose rendered output is substituted into the template
     * @param string $template Sprintf template with zero or one %s placeholder
     */
    public function __construct(private Op $origin, private string $template) {}

    #[Override]
    public function rendered(): string
    {
        return sprintf($this->template, $this->origin->rendered());
    }
}
