<?php

namespace App\Controller;

use App\DTO\CreateMessage;
use App\Factory\MessageFactory;
use App\Repository\ConversationRepository;
use App\service\PusherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;


final class MessageController extends AbstractController
{
    public function __construct(
        private readonly MessageFactory $messageFactory,
        private ConversationRepository $conversationRepository
    ) {}

    #[Route('/messages', name: 'message.create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateMessage $payload,
        PusherService $pusherService,
    ): Response {


        $conversation = $this->conversationRepository->find($payload->conversationId);
        $message = $this->messageFactory->create(
            conversation: $conversation,
            author: $this->getUser(),
            content: $payload->content
        );

        $messageData = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'conversationId' => $message->getConversation()->getId(),
            'author' => [
                'id' => $message->getAuthor()->getId(),
                'name' => $message->getAuthor()->getEmail()
            ],
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        $pusherService->trigger(
            'chat',
            'message-created',
            $messageData
        );

        return new Response('', Response::HTTP_CREATED);
    }
}
