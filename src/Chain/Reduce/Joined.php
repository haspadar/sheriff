<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Reduce;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Reduced;
use Override;

/**
 * Concatenates the rendered outputs of multiple Ops with a glue string.
 *
 * Example:
 *
 *     (new Joined([
 *         new NeonBool(new BoolValue(true)),
 *         new NeonBool(new BoolValue(false)),
 *     ], ', '))->rendered();
 *     // "true, false"
 */
final readonly class Joined implements Reduced
{
    /**
     * Initializes with the parts to render and the glue between them.
     *
     * @param list<Op> $parts Source ops whose rendered outputs are joined in order
     * @param string $glue String inserted between consecutive rendered outputs
     */
    public function __construct(private array $parts, private string $glue) {}

    #[Override]
    public function rendered(): string
    {
        return implode(
            $this->glue,
            array_map(static fn(Op $part): string => $part->rendered(), $this->parts),
        );
    }
}
