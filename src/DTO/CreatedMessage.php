<?php

namespace App\DTO;

final class CreateMessage
{
    public readonly string $content;
    public readonly int $conversationId;

    public function __construct(string $content, int $conversationId)
    {
        $this->content = $content;
        $this->conversationId = $conversationId;
    }
}
