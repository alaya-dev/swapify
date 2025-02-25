<?php

namespace App\Controller;

use App\Factory\ConversationFactory;
use App\Repository\ConversationRepository;
use App\Repository\OffreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConversationController extends AbstractController
{
    public function __construct(private readonly ConversationRepository $conversationRepository, private readonly ConversationFactory $conversationFactory, private readonly OffreRepository $offerRepository, private readonly UserRepository $userRepository) {}

    #[Route('/conversation/{recipient}', name: 'conversation.show')]
    public function show($recipient): Response
    {
        $currentUser = $this->getUser();
        $recipientUser = $this->userRepository->find($recipient);
        if (!$recipientUser) {
            throw $this->createNotFoundException('Recipient user not found.');
        }

      
        /*$offer = $this->offerRepository->findOneBy([
            'offerMaker' => $currentUser,
            'annonceOwner' => $recipientUser,
        ]);

        if (!$offer) {
            $this->addFlash('error', 'You must send an offer to this user before contacting them.');
            return $this->redirectToRoute('app_annonce_index');
        }*/

        /*if ($offer->getStatus() !== 'accepted') {
            $this->addFlash('error', 'Your offer must be accepted before you can contact this user.');
            return $this->redirectToRoute('app_dashboard_client');
        }*/

        $conversation = $this->conversationRepository->findByUsers($currentUser, $recipientUser);
        if (!$conversation) {
            $conversation = $this->conversationFactory->create($currentUser, $recipientUser);
        }

        $conversations = $this->conversationRepository->findConversationsForUser($currentUser);

        return $this->render('dashboard/conversation.html.twig', [
            'conversation' => $conversation,
            'currentUser' => $currentUser,
            'conversations' => $conversations,
            'recipientID' => $recipient,
        ]);
    }
}
