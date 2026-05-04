<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain;

/**
 * Marker for chain ops that expose an ordered list of inner ops.
 *
 * Implementations carry a list of Op objects between pipeline stages, so map
 * and reduce operations can transform or fold the parts before rendering.
 * Calling rendered() directly on a Listed is a misuse: implementations are
 * expected to throw SheriffException, since collapsing parts into a string
 * is the job of an explicit Reduced step (typically Joined) further down
 * the pipeline.
 */
interface Listed extends Op
{
    /**
     * Returns the inner ops in their pipeline order.
     *
     * @return list<Op> Inner ops the next pipeline stage will iterate over
     */
    public function parts(): array;
}
