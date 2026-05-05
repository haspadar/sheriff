<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain\Reduce;

use Haspadar\Sheriff\Chain\Op;
use Haspadar\Sheriff\Chain\Reduced;
use Haspadar\Sheriff\SheriffException;
use Override;

/**
 * Renders only the first part of a list, ignoring the rest.
 *
 * Example:
 *
 *     (new First([
 *         new StringText(new StringValue('8.3')),
 *         new StringText(new StringValue('8.4')),
 *     ]))->rendered();
 *     // "8.3"
 */
final readonly class First implements Reduced
{
    /**
     * Initializes with the parts whose first element will be rendered.
     *
     * @param list<Op> $parts Ordered ops; only the first is rendered
     */
    public function __construct(private array $parts) {}

    #[Override]
    public function rendered(): string
    {
        if ($this->parts === []) {
            throw new SheriffException('First cannot render an empty list');
        }

        return $this->parts[0]->rendered();
    }
}
