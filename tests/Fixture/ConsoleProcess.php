<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Tests\Fixture;

final readonly class ConsoleProcess
{
    private const array ALLOWED = ['info', 'success', 'error', 'muted'];

    private string $stdout;

    private string $stderr;

    public function __construct(string $method, string $text)
    {
        if (!in_array($method, self::ALLOWED, true)) {
            throw new \InvalidArgumentException(
                sprintf('Method %s is not allowed', var_export($method, true)),
            );
        }

        $script = sprintf(
            'require %s; (new \Haspadar\Sheriff\Output\Console())->%s(%s);',
            var_export(dirname(__DIR__, 2) . '/vendor/autoload.php', true),
            $method,
            var_export($text, true),
        );

        $proc = proc_open(
            [PHP_BINARY, '-r', $script],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        if (!is_resource($proc)) {
            throw new \RuntimeException('Failed to start subprocess');
        }

        fclose($pipes[0]);
        $this->stdout = (string) stream_get_contents($pipes[1]);
        $this->stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                sprintf('Subprocess exited with code %d: %s', $exitCode, $this->stderr),
            );
        }
    }

    public function stdout(): string
    {
        return $this->stdout;
    }

    public function stderr(): string
    {
        return $this->stderr;
    }
}
