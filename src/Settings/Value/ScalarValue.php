<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Marker for scalar configuration values: int, float, bool, string.
 *
 * Used to type Patch implementations that accept any scalar regardless of
 * the concrete subtype.
 */
interface ScalarValue extends Value {}
