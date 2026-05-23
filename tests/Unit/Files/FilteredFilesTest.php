<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Unit\Files;

use Haspadar\Sheriff\File\File;
use Haspadar\Sheriff\Files\FilteredFiles;
use Haspadar\Sheriff\Files\TextFiles;
use Haspadar\Sheriff\Tests\Constraint\Files\HasFiles;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilteredFilesTest extends TestCase
{
    #[Test]
    public function returnsOnlyMatchingFilesWhenPredicateFiltersOut(): void
    {
        self::assertThat(
            new FilteredFiles(
                new TextFiles([
                    'pre-push' => 'exit 1',
                    'pre-push-sheriff' => 'exit 2',
                    'commit-msg' => 'exit 3',
                ]),
                static fn(File $file): bool => !str_ends_with($file->name(), 'pre-push'),
            ),
            new HasFiles([
                'pre-push-sheriff' => 'exit 2',
                'commit-msg' => 'exit 3',
            ]),
            'FilteredFiles must exclude files that do not satisfy the predicate',
        );
    }

    #[Test]
    public function returnsEmptyListWhenNoFilesMatchPredicate(): void
    {
        self::assertThat(
            new FilteredFiles(
                new TextFiles([
                    'pre-push' => 'exit 0',
                ]),
                static fn(File $file): bool => str_starts_with($file->name(), 'commit'),
            ),
            new HasFiles([]),
            'FilteredFiles must return empty list when no files satisfy the predicate',
        );
    }

    #[Test]
    public function returnsAllFilesWhenAllMatchPredicate(): void
    {
        self::assertThat(
            new FilteredFiles(
                new TextFiles([
                    'pre-push' => 'run push checks',
                    'pre-commit' => 'run commit checks',
                ]),
                static fn(File $file): bool => str_starts_with($file->name(), 'pre-'),
            ),
            new HasFiles([
                'pre-push' => 'run push checks',
                'pre-commit' => 'run commit checks',
            ]),
            'FilteredFiles must return all files when every file satisfies the predicate',
        );
    }
}
