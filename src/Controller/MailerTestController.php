<?php

namespace App\Controller;

use App\Service\MailerMailJetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MailerTestController extends AbstractController
{


    #[Route('/mailer/test', name: 'app_mailer_test')]
    public function sendEmail(MailerMailJetService $mj): Response
    {
        // HTML content with an image hosted online
        
        $htmlContent = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email with Image</title>
            </head>
            <body>
                <h1>Hello from Mailjet!</h1>
                <p>This is a test email using Mailjet with Symfony!</p>
               
            </body>
            </html>';
    
        // Send the email
        $mj->sendEmail(
            'alouioussema697@gmail.com', 
            'Hello from Mailjet',
            $htmlContent,
           
        );
    
        return new Response('Email sent successfully!');
    }



}
