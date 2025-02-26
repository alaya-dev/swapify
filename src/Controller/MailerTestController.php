<?php

namespace App\Controller;

use App\Service\mailerMailJetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MailerTestController extends AbstractController
{


    #[Route('/mailer/test', name: 'app_mailer_test')]
    public function sendEmail(mailerMailJetService $mj): Response
    {

        $mj->sendEmail(
            'Karim.Mokaddem@esprit.tn', 
            'Hello from Mailjet',
            'This is a test email using Mailjet with Symfony!'
        );

        return new Response('Email sent successfully!');
    }



}
