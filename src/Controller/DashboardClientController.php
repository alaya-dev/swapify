<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Enum\EtatEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardClientController extends AbstractController
{
    #[Route('/dashboard/client', name: 'app_dashboard_client')]
    public function index(Request $request): Response
    {


        return $this->render('dashboard/accueil_client.html.twig', [
            'controller_name' => 'DashboardClientController',
        ]);
    }


    #[Route('/dashboard/client/souk', name: 'souk')]
    public function souk(): Response
    {
        return $this->render('dashboard/souk.html.twig');
    }

    #[Route('/dashboard/client/annonce', name: 'annonce')]
    public function annonce(): Response
    {
        return $this->render('dashboard/annonce.html.twig');
    }

    #[Route('/dashboard/client/blog', name: 'blog')]
    public function blog(EntityManagerInterface $entityManager): Response
    {
        $acceptedBlogs = $entityManager->getRepository(Blog::class)->findBy(
            ['statut' => EtatEnum::Acceptée],
            ['id' => 'DESC'] // Order by id in descending order
        );
        return $this->render('dashboard/blog.html.twig', [
            'blogs' => $acceptedBlogs,
        ]);
    }

    #[Route('/dashboard/client/evenment', name: 'evenment')]
    public function evenment(): Response
    {
        return $this->render('dashboard/evenment.html.twig');
    }

    #[Route('/dashboard/client/livraison', name: 'livraison')]
    public function livraison(): Response
    {
        return $this->render('dashboard/livraison.html.twig');
    }

    #[Route('/dashboard/client/conversation', name: 'conversation')]
    public function conversation(): Response
    {
        return $this->render('dashboard/conversation.html.twig');
    }
}
