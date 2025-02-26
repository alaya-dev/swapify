<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/verification', name: 'please-verify-email')]
    public function index(): Response
    {
        return $this->render('/admin/please-verify-email.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }


    /*
    // Route pour l'enregistrement d'un utilisateur
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        // Si un utilisateur est déjà connecté, rediriger vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = new User();
        // Création du formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // Attribution du rôle par défaut
            $user->setRoles(['ROLE_CLIENT']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('mailer@mailer.de', 'swapy boot'))
                    ->to($user->getEmail())
                    ->subject('S"il vous plait confirmez votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );  

            // Authentification automatique de l'utilisateur après son inscription
            $this->addFlash('success', 'Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception (et éventuellement votre dossier spam) pour confirmer votre adresse email.');

             // Redirection vers la page de connexion
             return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }*/

    //register with mailJet confirmation
    // Route pour l'enregistrement d'un utilisateur
#[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    UserPasswordHasherInterface $userPasswordHasher,
    UserAuthenticatorInterface $userAuthenticator,
    AppAuthenticator $authenticator,
    EntityManagerInterface $entityManager,  
      EmailVerifier $ev // Inject the new service
): Response {
    // Si un utilisateur est déjà connecté, rediriger vers la page d'accueil
    if ($this->getUser()) {
        return $this->redirectToRoute('app_login');
    }

    $user = new User();
    // Création du formulaire d'inscription
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );

        // Attribution du rôle par défaut
        $user->setRoles(['ROLE_CLIENT']);
        $entityManager->persist($user);
        $entityManager->flush();

        // Envoi de l'email de confirmation avec MailJet
        $ev->sendEmailConfirmation('app_verify_email', $user);

        $this->addFlash('success', 'Un email de vérification a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception (et éventuellement votre dossier spam) pour confirmer votre adresse email.');

        // Redirection vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request, 
        UserRepository $userRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        try {
            // Récupérer l'ID de l'utilisateur depuis l'URL
            $userId = $request->query->get('id');
    
            if (!$userId) {
                throw new \Exception('Aucun identifiant utilisateur trouvé.');
            }
    
            // Trouver l'utilisateur en base de données
            $user = $userRepository->find($userId);
    
            if (!$user) {
                throw $this->createNotFoundException('Utilisateur introuvable.');
            }
    
            // Vérifier l'email avec l'utilisateur récupéré
            $this->emailVerifier->handleEmailConfirmation($request, $user);
    
            // Marquer l'utilisateur comme vérifié
            $user->setIsVerified(true);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre adresse e-mail a été vérifiée.');
            return $this->redirectToRoute('app_login');
      } catch (VerifyEmailExceptionInterface $exception) {
             // Lien expiré → afficher un message et proposer le renvoi
            $this->addFlash('warning', 'Le lien de vérification a expiré. Renvoyez un nouvel email.');
            return $this->redirectToRoute('app_login', ['id' => $userId]);

        }}
        

        #[Route('/resend-verification-email', name: 'app_resend_verification_email')]
        public function resendVerificationEmail(
            Request $request,
            UserRepository $userRepository,
            EmailVerifier $emailVerifier
        ): Response {
            // Récupérer l'ID de l'utilisateur depuis l'URL ou la session
            $userId = $request->query->get('id');

            if (!$userId) {
                $this->addFlash('error', 'Aucun identifiant utilisateur trouvé.');
                return $this->redirectToRoute('app_register');
            }

            // Trouver l'utilisateur en base de données
            $user = $userRepository->find($userId);

            if (!$user) {
                $this->addFlash('error', 'Utilisateur introuvable.');
                return $this->redirectToRoute('app_register');
            }

            if ($user->isVerified()) {
                $this->addFlash('info', 'Votre compte est déjà vérifié Vous pouvez vous connecter.');
                return $this->redirectToRoute('app_login');
            }

            // Générer et envoyer un nouvel email de vérification
            $email = (new TemplatedEmail())
                ->from(new Address('mailer@mailer.de', 'swapy boot'))
                ->to($user->getEmail())
                ->subject('Renvoyer la vérification de l’email')
                ->htmlTemplate('registration/confirmation_email.html.twig');

            $emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);

            $this->addFlash('success', 'Un nouvel email de vérification a été envoyé.');
            return $this->redirectToRoute('app_login');
        }
    }
