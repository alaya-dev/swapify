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
    //#[Route('/dashboard', name: 'app_dashboard')]
    //public function index(): Response
    //{
      //  return $this->render('include.html.twig', [
        //    'controller_name' => 'DashboardController',
        //]);
    //}

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
    public function dashboard(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        // Récupérer les statistiques générales
        $totalAdmins = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->getQuery()
            ->getSingleScalarResult();

        $totalClients = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_CLIENT"%')
            ->getQuery()
            ->getSingleScalarResult();

        $verifiedClients = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :verified')
            ->setParameter('role', '%"ROLE_CLIENT"%')
            ->setParameter('verified', true)
            ->getQuery()
            ->getSingleScalarResult();

        $unverifiedClients = $userRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :verified')
            ->setParameter('role', '%"ROLE_CLIENT"%')
            ->setParameter('verified', false)
            ->getQuery()
            ->getSingleScalarResult();

        // Exécuter une requête SQL pour récupérer les clients inscrits par jour
        $conn = $entityManager->getConnection();

        $sql = "SELECT DATE(created_at) as day, COUNT(id) as count 
                FROM user 
                WHERE roles LIKE :role 
                GROUP BY day 
                ORDER BY day ASC";

        $stmt = $conn->executeQuery($sql, ['role' => '%"ROLE_CLIENT"%']);
        $dailyClients = $stmt->fetchAllAssociative();

        $days = []; // Tableau pour stocker les résultats par jour

        // Remplir le tableau avec les résultats de la requête
        foreach ($dailyClients as $data) {
            $days[$data['day']] = $data['count'];
        }

        // Créer des labels de jours sous forme de tableau (format 'Y-m-d' par exemple)
        $dayLabels = array_keys($days); // Récupérer les dates sous forme de labels
        $clientCounts = array_values($days); // Convertir en tableau de valeurs (comptes)

        // Préparer les données pour Chart.js avec 2 datasets
        $chartData = [
            'labels' => array_merge(['Admins', 'Clients', 'Clients vérifiés', 'Clients non Vérifiés'], $dayLabels),
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => [$totalAdmins, $totalClients, $verifiedClients, $unverifiedClients] + array_fill(0, count($dayLabels), null), // Remplissage pour aligner avec les jours
                    'backgroundColor' => ['#FF5733', '#33C3FF', '#28a745', '#dc3545'],
                    'borderColor' => ['#FF5733', '#33C3FF', '#28a745', '#dc3545'],
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Clients Inscrits par Jour',
                    'data' => array_fill(0, 4, null) + $clientCounts, // Décaler les valeurs pour aligner avec les jours
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ]
            ]
        ];

        // Préparer les options du graphique
        $chartOptions = [
            'responsive' => true,
            'scales' => [
                'y' => ['beginAtZero' => true]
            ]
        ];

        // Passer les données à Twig pour affichage
        return $this->render('dashboard/accueil.html.twig', [
            'chartData' => $chartData,
            'chartOptions' => $chartOptions
        ]);
    }


}