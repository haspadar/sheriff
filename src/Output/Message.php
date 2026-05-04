<?php

declare(strict_types=1);

namespace Haspadar\Sheriff\Output;

/**
 * An immutable text message with a plain string body.
 */
final readonly class Message
{
    /**
     * Initializes with the message text.
     *
     * @param string $body Raw message text
     */
    public function __construct(private string $body) {}

    /** Returns the message text. */
    public function body(): string
    {
        return $this->body;
    }
}
