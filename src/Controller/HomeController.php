<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{


    #[Route('/', name: 'app_home')]
    public function index(AnnonceRepository $annonceRepository,CategorieRepository $categorieRepository): Response
    {


        $trendingCategories = $categorieRepository->findTopTrendingCategories(3);
        return $this->render('landing.html.twig', [
            'controller_name' => 'HomeController',
            'annonces'=>$annonceRepository->findValiderAnnonces(),
            'categories'=>$categorieRepository->findAll(),
            'trendingCategories'=>$trendingCategories,

        ]);
    }
    #[Route('/info', name: 'info')]
    public function info()
    {

        phpinfo();
        
    }

   
}
