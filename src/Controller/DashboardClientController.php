<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\ProfileType;

class DashboardClientController extends AbstractController
{
    #[Route('/dashboard/client', name: 'app_dashboard_client')]
    public function index(Request $request): Response
    {

        $user = $this->getUser();
       /* $session = $request->getSession();
        $session->set('test_key', 'test_value');
        dd($session);
        return new Response('Session test: ' . $session->get('test_key'));*/

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);    

        return $this->render('dashboard/accueil_client.html.twig', [
            'controller_name' => 'DashboardClientController',
            'form' => $form->createView(),
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
    public function blog(): Response
    {
        return $this->render('dashboard/blog.html.twig');
    }

    #[Route('/dashboard/client/evenment', name: 'evenment')]
    public function evenment(): Response
    {
        return $this->render('dashboard/evenment.html.twig');
    }

   
    #[Route('/dashboard/client/conversation', name: 'conversation')]
    public function conversation(): Response
    {
        return $this->render('dashboard/conversation.html.twig');
    }
}