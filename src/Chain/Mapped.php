<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain;

/**
 * Marker for chain ops that transform one rendered string into another.
 *
 * Implementations decorate a single Op and rewrite its rendered output
 * before it reaches the template (sprintf, replace, escape, etc.).
 */
interface Mapped extends Op {}
