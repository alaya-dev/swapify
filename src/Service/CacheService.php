<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function cacheMessage(int $conversationId, array $messageData): void
    {
        $this->cache->get('conversation_' . $conversationId, function () use ($messageData) {
            return $messageData;
        });
    }

    public function getCachedMessage(int $conversationId): ?array
    {
        return $this->cache->get('conversation_' . $conversationId, function () {
            return null; 
        });
    }

    public function clearCache(int $conversationId): void
    {
        $this->cache->delete('conversation_' . $conversationId);
    }
}
