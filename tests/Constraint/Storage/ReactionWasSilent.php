<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint\Storage;

use Haspadar\Sheriff\Tests\Fake\Storage\Reaction\FakeStorageReaction;
use PHPUnit\Framework\Constraint\Constraint;

final class ReactionWasSilent extends Constraint
{
    public function toString(): string
    {
        return 'recorded no created or updated paths';
    }

    protected function matches($other): bool
    {
        return $other instanceof FakeStorageReaction
            && $other->createdPaths() === []
            && $other->updatedPaths() === [];
    }

    protected function failureDescription($other): string
    {
        return 'reaction ' . $this->toString();
    }

    protected function additionalFailureDescription($other): string
    {
        if (!$other instanceof FakeStorageReaction) {
            return "\nBut object of type " . get_debug_type($other) . ' was given';
        }

        return "\nCreated: " . var_export($other->createdPaths(), true)
            . "\nUpdated: " . var_export($other->updatedPaths(), true);
    }
}
