<?php

namespace App\Controller;

use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('include.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/profileDetails', name: 'profile')]
    public function profile1(): Response
    {
        return $this->render('dashboard/profile_user/show-profil.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/profile/{id}', name: 'profil_user')]
    public function profile(UserRepository $userRepo, RatingRepository $ratingRepo, int $id): Response
    {
        $user = $userRepo->find($id);
        $avgRating = $ratingRepo->getAverageRating($user) ?? 0;
    
        return $this->render('dashboard/profile_user/show-profil.html.twig', [
            'user' => $user,
            'avgRating' => $avgRating
        ]);
    }
}
