<?php

namespace App\DTO;


readonly final class CreatedMessage
{
    public function __construct(public string $content, public int $conversationId) {}
}
