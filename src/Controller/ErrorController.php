<?php
// src/Controller/ErrorController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    /**
     * @Route("/error403", name="error403")
     */
    public function error403()
    {
        return $this->render('bundles/TwigBundle/Exception/error403.html.twig');
    }

    /**
     * @Route("/error404", name="error404")
     */
    public function error404()
    {
        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
    }

    /**
     * @Route("/error500", name="error500")
     */
    public function error500()
    {
        return $this->render('bundles/TwigBundle/Exception/error500.html.twig');
    }
}

