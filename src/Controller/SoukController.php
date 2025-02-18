<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SoukController extends AbstractController
{
    #[Route('/souk', name: 'app_souk')]
    public function index(): Response
    {
        return $this->render('livraison/laivraison.html.twig', [
            'controller_name' => 'SoukController',
        ]);
    }
}
