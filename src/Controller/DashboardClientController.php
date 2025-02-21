<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Offre;
use App\Entity\Souk;
use App\Repository\ConversationRepository;
use App\Repository\OffreRepository;
use App\Repository\ProductRepository;
use App\Repository\SoukRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardClientController extends AbstractController
{
    public function __construct(private readonly ConversationRepository $conversationRepository) {}

    #[Route('/dashboard/client', name: 'app_dashboard_client')]
    public function rendre(OffreRepository $offerRepository, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $current_user = $this->getUser();
        $conversations = $entityManager->getRepository(Conversation::class)->findConversationsForUser($current_user);
        $my_offers = $offerRepository->findBy(['offerMaker' => $current_user]);
        $other_offers = $offerRepository->findBy([
            'annonceOwner' => $current_user,
            'status' => ['accepted', 'pending']
        ]);
        $my_products = $productRepository->findBy(['owner' => $current_user]);
        return $this->render('dashboard/accueil_client.html.twig', [
            'my_offers' => $my_offers,
            'other_offers' => $other_offers,
            'my_products' => $my_products,
            'conversations' => $conversations,
        ]);
    }


    #[Route('/delete/{id}', name: 'offer_delete', methods: ['POST'])]
    public function deleteOffer($id, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(Offre::class)->find($id);
        if ($offer->getOfferMaker() !== $this->getUser()) {
            $offer->setStatus('refused');
            $em->flush();
            $this->addFlash('success', 'Offer status updated to "refused".');
        } else {
            $em->remove($offer);
            $em->flush();
            $this->addFlash('success', 'Offer deleted successfully.');
        }
        return $this->redirectToRoute('app_dashboard_client');
    }

    #[Route('/accepte/{id}', name: 'offer_accepte', methods: ['POST'])]
    public function accepte($id, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(Offre::class)->find($id);
        if ($offer->getOfferMaker() !== $this->getUser()) {
            $offer->setStatus('accepted');
            $em->flush();
            $this->addFlash('success', 'Offer status updated to "refused".');
        }

        return $this->redirectToRoute('app_dashboard_client');
    }

    #[Route('/update/{id}', name: 'offer_update', methods: ['POST'])]
    public function updateOffer($id, Request $request, EntityManagerInterface $entityManager, EntityManagerInterface $em): Response
    {
        $offer = $em->getRepository(Offre::class)->find($id);
        if (!$offer) {
            $this->addFlash('error', 'Offer not found.');
            return $this->redirectToRoute('app_dashboard_client');
        }
        $description = $request->request->get('description');
        if ($description !== null) {
            $offer->setDescription($description);
            $entityManager->flush();
        } else {
            $this->addFlash('warning', 'Description cannot be empty.');
        }

        return $this->redirectToRoute('app_dashboard_client');
    }

    // all souke
    #[Route('/dashboard/client/souks', name: 'all_souk')]
    public function getAllSouk(SoukRepository $soukRepository): Response
    {
        return $this->render('souk/index.html.twig', [
            'souks' => $soukRepository->findAll(),
        ]);
    }

    // souke details 
    #[Route('/dashboard/client/souk/{id}', name: 'souk_details')]
    public function getSingleSouk(int $id, EntityManagerInterface $entityManager): Response
    {
        $souk = $entityManager->getRepository(Souk::class)->find($id);
        if (!$souk) {
            throw $this->createNotFoundException('Souk not found');
        }

        $products = $souk->getProducts();
        return $this->render('souk/main_souk.html.twig', [
            'products' => $products
        ]);
    }

    // single souke
    #[Route('/dashboard/client/souk', name: 'souk_detail')]
    public function getUserSouk(): Response
    {
        return $this->render('souk/main_souk.html.twig');
    }

    // joinned souk for user 
    #[Route('/dashboard/client/soukUser', name: 'souk')]
    public function joinSoukUser(): Response
    {
        $user = $this->getUser();
        $souks = $user->getSouks();

        return $this->render('dashboard/souk.html.twig', [
            'souks' => $souks
        ]);
    }

    #[Route('/dashboard/client/annonce', name: 'annonce')]
    public function annonce(): Response
    {
        return $this->render('dashboard/annonce.html.twig');
    }

    #[Route('/dashboard/client/blog', name: 'blog')]
    public function blog(): Response
    {
        return $this->render('dashboard/blog.html.twig');
    }

    #[Route('/dashboard/client/evenment', name: 'evenment')]
    public function evenment(): Response
    {
        return $this->render('dashboard/evenment.html.twig');
    }

   
    #[Route('/dashboard/client/conversation', name: 'conversation')]
    public function conversation(EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();
        $conversations = $entityManager->getRepository(Conversation::class)->findConversationsForUser($currentUser);
        $conversationDetails = [];

        foreach ($conversations as $conversation) {
            $participants = $conversation->getUsers();
            $messages = $conversation->getMessages();

            $otherUser = null;
            foreach ($participants as $user) {
                if ($user !== $currentUser) {
                    $otherUser = $user;
                    break;
                }
            }
            $conversationDetails[$conversation->getId()] = [
                'participants' => $participants,
                'messages' => $messages,
                'otherUser' => $otherUser,
                'lastConnexion' => $otherUser ? $otherUser->getLastConnexion() : null,
            ];
        }
        return $this->render('dashboard/conversation.html.twig', [
            'conversations' => $conversations,
            'currentUser' => $currentUser,
            'conversation' => $conversation,
        ]);
    }
}
