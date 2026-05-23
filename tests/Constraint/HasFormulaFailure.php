<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Constraint;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\SheriffException;
use Override;
use PHPUnit\Framework\Constraint\Constraint;

final class HasFormulaFailure extends Constraint
{
    public function __construct(
        private readonly string $fileName,
        private readonly string $formulaPart,
        private readonly string $reasonPart,
    ) {}

    #[Override]
    protected function matches(mixed $other): bool
    {
        if (!$other instanceof File) {
            return false;
        }

        try {
            $other->contents();

            return false;
        } catch (SheriffException $e) {
            $message = $e->getMessage();

            return str_contains($message, $this->fileName)
                && str_contains($message, $this->formulaPart)
                && str_contains($message, $this->reasonPart);
        }
    }

    public function toString(): string
    {
        return 'has formula failure with file context';
    }
}
