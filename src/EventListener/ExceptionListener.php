<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ExceptionListener
{
    private RouterInterface $router;
    private Environment $twig;

    public function __construct(RouterInterface $router, Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Vérifie si l'erreur est une erreur 404 (Not Found)
        if ($exception instanceof NotFoundHttpException) {
            // Soit on redirige vers une page spécifique
            $response = new RedirectResponse($this->router->generate('error404'));

            // Soit on affiche directement un contenu personnalisé (sans redirection)
            // $content = $this->twig->render('errors/404.html.twig');
            // $response = new Response($content, Response::HTTP_NOT_FOUND);

            $event->setResponse($response);
        }
    }
}
