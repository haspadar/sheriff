<?php

declare(strict_types=1);

namespace Haspadar\Piqule\Chain\Parse;

use Haspadar\Piqule\Chain\Op;
use Haspadar\Piqule\Settings\Settings;

/**
 * One stage of a template pipeline expressed as `Name(args)`.
 *
 * Implementations turn themselves into a Chain Op given the settings context
 * and the ops produced by earlier pipeline stages. Pipeline source formulas
 * ignore the previous ops list; map and reduce formulas decorate it.
 *
 * When driven by PipelineOp the previous list always carries at most one op
 * — the immediate predecessor's result. The list shape exists so reduce
 * formulas can be constructed standalone with several ops to combine.
 */
interface Formula
{
    /**
     * Builds the Op for this pipeline stage on top of earlier stages.
     *
     * @param list<Op> $previous Ops produced by preceding pipeline stages, empty for the head
     * @param Settings $settings Settings context the formula reads from
     * @return Op Op that renders this stage's contribution
     */
    public function op(array $previous, Settings $settings): Op;
}
