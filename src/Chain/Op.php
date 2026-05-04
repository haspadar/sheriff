<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain;

/**
 * One step in a template chain that produces a string for substitution.
 */
interface Op
{
    /**
     * Returns the rendered string this step contributes to the template.
     */
    public function rendered(): string;
}
