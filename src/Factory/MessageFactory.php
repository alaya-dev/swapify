<?php

namespace App\Factory;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MessageFactory
{

    public function __construct(private readonly EntityManagerInterface $entityManagerInterface) {}

    public function create(Conversation $conversation, User $author, string $content)
    {
        $message = new Message();
        $message->setConversation($conversation);
        $message->setAuthor($author);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTimeImmutable());
        $this->entityManagerInterface->persist($message);
        $this->entityManagerInterface->flush();
        return $message;
    }
}
