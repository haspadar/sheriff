<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain;

/**
 * Marker for chain ops that fold multiple rendered strings into one.
 *
 * Implementations take a list of Op objects and produce a single rendered
 * string out of their concatenation, first element, and so on.
 */
interface Reduced extends Op {}
