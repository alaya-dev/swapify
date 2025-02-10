<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardClientController extends AbstractController
{
    #[Route('/dashboard/client', name: 'app_dashboard_client')]
    public function index(): Response
    {
        return $this->render('dashboard/accueil_client.html.twig', [
            'controller_name' => 'DashboardClientController',
        ]);
    }

    
}
