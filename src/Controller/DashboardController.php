<?php

namespace App\Controller;

use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AnnonceRepository;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;

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



    private $annonceRepository;
    private $blogRepository;


    // Injection des dépendances dans le constructeur
    public function __construct(AnnonceRepository $annonceRepository,BlogRepository $blogRepository)
    {
        $this->annonceRepository = $annonceRepository;
        $this->blogRepository = $blogRepository;
    }


    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(UserRepository $userRepository,AnnonceRepository $annonceRepository,BlogRepository $blogRepository ,EntityManagerInterface $entityManager)
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



      // Statistiques des annonces par statut
      $acceptedAnnonces = $this->annonceRepository->countAcceptedAnnonces();
      $rejectedAnnonces = $this->annonceRepository->countRejectedAnnonces();
      $pendingAnnonces = $this->annonceRepository->countPendingAnnonces();


      // Statistiques des annonces par statut
      $acceptedBlogs = $this->blogRepository->countAcceptedBlogs();
      $rejectedBlogs = $this->blogRepository->countRejectedBlogs();
      $pendingBlogs = $this->blogRepository->countPendingBlogs();


    
        // Préparer les données pour Chart.js
        $chartData = [
            'labels' => ['Admins', 'Clients', 'Vérifiés', 'Non Vérifiés', 'Bannis', 'Non Bannis'],
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => [$totalAdmins, $totalClients, $verifiedClients, $unverifiedClients, 
                    $bannedClients, $unbannedClients ],
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


        // Données pour le graphique des annonces
        $annonceChartData = [
            'labels' => ['Acceptées', 'Rejetées', 'En Attente'],
            'datasets' => [
                [
                    'label' => 'Statistiques des Annonces',
                    'data' => [$acceptedAnnonces, $rejectedAnnonces, $pendingAnnonces],
                    'backgroundColor' => ['#ffcc80', '#ffb3b3', '#ffeb99'],
                    'borderColor' => ['#ffcc80', '#ffb3b3', '#ffeb99'],
                    'borderWidth' => 1,
                ]
            ]
        ];


        // Préparer les données pour le graphique en camembert
        $pieChartData = [
            'labels' => ['Acceptées', 'Rejetées', 'En Attente'],
            'datasets' => [
                [
                    'label' => 'Statistiques des Blogs',
                    'data' => [$acceptedBlogs, $rejectedBlogs, $pendingBlogs],
                    'backgroundColor' => ['#ffcc80', '#ffb3b3', '#ffeb99'], // Couleurs light pour les blogs
                    'borderColor' => ['#ffcc80', '#ffb3b3', '#ffeb99'],
                    'borderWidth' => 1,
                ]
            ]
        ];
    
        // Passer les données à Twig
        return $this->render('dashboard/accueil.html.twig', [
            'chartData' => $chartData,
            'chartOptions' => $chartOptions,
            'annonceChartData' => $annonceChartData, 
            'acceptedAnnonces' => $acceptedAnnonces,
            'rejectedAnnonces' => $rejectedAnnonces,
            'pendingAnnonces' => $pendingAnnonces,
            'acceptedBlogs' => $acceptedBlogs,
            'rejectedBlogs' => $rejectedBlogs,
            'pendingBlogs' => $pendingBlogs,
            'pieChartData' => $pieChartData,

        ]);
    }



















}
