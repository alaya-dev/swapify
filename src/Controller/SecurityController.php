<?php

namespace App\Controller;

use App\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class SecurityController extends AbstractController
{

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        

        $form = $this->createForm(TaskType::class);
        $form->handleRequest($request);

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        // Récupère le paramètre 'error' de l'URL si présent
        if ($request->query->get('error')) {
            $this->addFlash('error', $request->query->get('error'));
        }

        if ($error) {
            $this->addFlash('error', 'données invalides');
        }
        
            // 🔹 Retourner toujours une réponse
        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
    }
}
    