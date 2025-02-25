<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RatingController extends AbstractController
{
    #[Route('/rating', name: 'app_rating')]
    public function index(): Response
    {
        return $this->render('rating/index.html.twig', [
            'controller_name' => 'RatingController',
        ]);
    }
    #[Route('/rate/{idRecepteur}', name: 'rate_user', methods: ['POST'])]
    public function rateUser(Request $request, EntityManagerInterface $em, UserRepository $userRepo, int $idRecepteur): Response
    {
        $idDonneur = $this->getUser(); // L'utilisateur connecté
        $recepteur = $userRepo->find($idRecepteur);

        if (!$recepteur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $existingRating = $em->getRepository(Rating::class)->findOneBy([
            'idDonneur' => $idDonneur,
            'idRecepteur' => $recepteur
        ]);

        if ($existingRating) {
            $this->addFlash('warning', 'Vous avez déjà noté cet utilisateur.');
            return $this->redirectToRoute('profil_user', ['id' => $idRecepteur]);
        }

        $rating = new Rating();
        $rating->setIdDonneur($idDonneur);
        $rating->setIdRecepteur($recepteur);
        $rating->setRating($request->request->get('rating'));

        $em->persist($rating);
        $em->flush();

        $this->addFlash('success', 'Note enregistrée avec succès.');
        return $this->redirectToRoute('profil_user', ['id' => $idRecepteur]);
    }
}
