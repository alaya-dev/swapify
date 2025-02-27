<?php

namespace App\Controller;

use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;


class DashboardController extends AbstractController
{
    #[Route('/statist', name: 'statistique')]
    public function index(): Response
    {
     return $this->render('statistique.html.twig', [
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

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(UserRepository $userRepository)
    {
        // Récupérer le nombre d'administrateurs et de clients en utilisant QueryBuilder
        $totalAdmins = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->getQuery()
            ->getSingleScalarResult();
    
        $totalClients = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_CLIENT"%') // Utilisation de ROLE_CLIENT
            ->getQuery()
            ->getSingleScalarResult();
    
        // Récupérer le nombre de clients vérifiés et non vérifiés
        $verifiedClients = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :verified') // Remplacer isVerified par le nom du champ dans ta table
            ->setParameter('role', '%"ROLE_CLIENT"%') // Utilisation de ROLE_CLIENT
            ->setParameter('verified', true)
            ->getQuery()
            ->getSingleScalarResult();
    
        $unverifiedClients = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :verified') // Remplacer isVerified par le nom du champ dans ta table
            ->setParameter('role', '%"ROLE_CLIENT"%') // Utilisation de ROLE_CLIENT
            ->setParameter('verified', false)
            ->getQuery()
            ->getSingleScalarResult();


        
    // Clients bannis et non bannis
    $bannedClients = $userRepository->createQueryBuilder('u')
        ->select('count(u.id)')
        ->where('u.roles LIKE :role')
        ->andWhere('u.isBanned = :banned')
        ->setParameter('role', '%"ROLE_CLIENT"%')
        ->setParameter('banned', true)
        ->getQuery()
        ->getSingleScalarResult();

    $unbannedClients = $userRepository->createQueryBuilder('u')
        ->select('count(u.id)')
        ->where('u.roles LIKE :role')
        ->andWhere('u.isBanned = :banned')
        ->setParameter('role', '%"ROLE_CLIENT"%')
        ->setParameter('banned', false)
        ->getQuery()
        ->getSingleScalarResult();

    
        // Préparer les données pour Chart.js
        $chartData = [
            'labels' => ['Admins', 'Clients', 'Vérifiés', 'Non Vérifiés', 'Bannis', 'Non Bannis'],
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => [$totalAdmins, $totalClients, $verifiedClients, $unverifiedClients, $bannedClients, $unbannedClients],
                    'backgroundColor' => ['#ffcc99', '#a3d8ff', '#b8f2b8', '#ff9a9a', '#d3d3d3', '#f0e68c '],
                    'borderColor' => ['#ffcc99', '#a3d8ff', '#b8f2b8', '#ff9a9a', '#d3d3d3', '#f0e68c '],
                    'borderWidth' => 1,
                ]
            ]
        ];
    
        // Préparer les options du graphique (ex. responsive)
        $chartOptions = [
            'responsive' => true,
            'scales' => [
                'y' => ['beginAtZero' => true]
            ]
        ];
    
        // Passer les données à Twig
        return $this->render('dashboard/accueil.html.twig', [
            'chartData' => $chartData,
            'chartOptions' => $chartOptions,
        ]);
    }


}