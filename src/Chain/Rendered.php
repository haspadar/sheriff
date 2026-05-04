<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Chain;

/**
 * Marker for chain ops that act as format-specific sources.
 *
 * Implementations live under Chain\Render\<Format>\* and turn a Value into
 * its native representation in that format (neon, json, xml, php).
 */
interface Rendered extends Op {}
