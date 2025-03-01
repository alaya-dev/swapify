<?php
// src/Service/BadWordFilter.php
namespace App\Service;

class BadWordFilter
{
    private array $badWords;

    public function __construct(array $badWords)
    {
        $this->badWords = $badWords;
    }

    public function containsBadWords(string $text): bool
    {
        foreach ($this->badWords as $badWord) {
            if (stripos($text, $badWord) !== false) {
                return true;
            }
        }
        return false;
    }
}