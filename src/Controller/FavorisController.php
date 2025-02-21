<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Favoris;
use App\Repository\FavorisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FavorisController extends AbstractController
{


    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')" )]
    #[Route('/wishlist/add/{id}', name: 'wishlist_add')]
    public function addToWishlist(Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $wishlistRepo = $entityManager->getRepository(Favoris::class);
        $existingItem = $wishlistRepo->findOneBy(['user' => $user, 'annonces' => $annonce]);

        if (!$existingItem) {
            $wishlist = new Favoris();
            $wishlist->setUser($user);
            $wishlist->setAnnonces($annonce);
            $entityManager->persist($wishlist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_annonce_index');
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')" )]
    #[Route('/wishlist/remove/{id}', name: 'wishlist_remove')]
    public function removeFromWishlist(Favoris $wishlist, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || $wishlist->getUser() !== $user) {
            return $this->redirectToRoute('app_login');
        }

        $entityManager->remove($wishlist);
        $entityManager->flush();

        return $this->redirectToRoute('wishlist_show');
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')" )]
    #[Route('/wishlist', name: 'wishlist_show')]
    public function showWishlist(FavorisRepository $wishlistRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $wishlistItems = $wishlistRepository->findBy(['user' => $user]);

        return $this->render('favoris/index.html.twig', [
            'wishlistItems' => $wishlistItems
        ]);
    }
}
