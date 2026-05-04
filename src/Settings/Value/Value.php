<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Settings\Value;

/**
 * Marker for any configuration value produced by Settings.
 *
 * Holds raw data only. Behaviour (rendering, merging, serialization) lives
 * in dedicated decorators outside this namespace.
 */
interface Value {}
