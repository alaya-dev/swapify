<?php

namespace App\Security;

use App\Service\MailerMailJetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private MailerMailJetService $MailerMailJetService;


    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager,MailerMailJetService $MailerMailJetService)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
        $this->MailerMailJetService = $MailerMailJetService;

    }

    /*
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }*/


    //email de vérification avec mailJet karim.M
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );
    
        $signedUrl = $signatureComponents->getSignedUrl();
        $expiresAtMessageKey = $signatureComponents->getExpirationMessageKey();
        $expiresAtMessageData = $signatureComponents->getExpirationMessageData();
    
        $subject = 'Confirm Your Email Address';
        $content = sprintf(
            "<html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
            .header { font-size: 24px; font-weight: bold; color: #4CAF50; text-align: center; margin-bottom: 20px; }
            .button { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #4CAF50; text-decoration: none; border-radius: 5px; }
            .footer { margin-top: 20px; text-align: center; font-size: 14px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>Confirmation d'email</div>
            <p>Bonjour %s,</p>
            <p>Merci de vous être inscrit chez nous ! Pour finaliser votre inscription, veuillez confirmer votre adresse e-mail en cliquant sur le bouton ci-dessous :</p>
            <p style='text-align: center;'><a href='%s' class='button'>Confirmer l'email</a></p>
            <p>Ce lien expirera dans <strong>1 heure</strong>. Si vous n'avez pas fait cette demande, veuillez ignorer cet email.</p>
            <div class='footer'>
                <p>Si vous avez des questions, n'hésitez pas à <a href='swapifyMailer199@gmail.com'>contacter notre équipe d'assistance</a>.</p>
                <p>Cordialement,<br>Votre entreprise</p>
            </div>
        </div>
    </body>
</html>",
            $user->getPrenom(), 
            $signedUrl,
            $expiresAtMessageKey
        );
    
        $this->MailerMailJetService->sendEmail($user->getEmail(), $subject, $content);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
