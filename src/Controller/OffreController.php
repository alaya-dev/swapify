<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offre', name: 'offre_')]
final class OffreController extends AbstractController
{
    #[Route('/new', name: 'create', methods: ['POST'])]
    public function createOffre(Request $request, EntityManagerInterface $entityManager, AnnonceRepository $annonceRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['description'], $data['annonceId'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }
        $annonce = $annonceRepository->find($data['annonceId']);
        if (!$annonce) {
            return new JsonResponse(['error' => 'Annonce introuvable'], 404);
        }

        if ($data['description'] == '') {
            $this->addFlash('error', 'Veuillez entrer une description');
            return $this->redirectToRoute('app_annonce_index');
        };

        $offer = new Offre();
        $offer->setDescription($data['description']);
        $offer->setStatus('pending');
        $offer->setAnnonceName($annonce);
        $offer->setAnnonceOwner($annonce->getUser());
        $offer->setOfferMaker($currentUser);
        $entityManager->persist($offer);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Offre créée avec succès'], 201);
    }
}
