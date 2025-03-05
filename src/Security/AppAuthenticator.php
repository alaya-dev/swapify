<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;




class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private  UrlGeneratorInterface $urlGenerator,
     private UserRepository $userRepository,
     private Security $security,
     private RequestStack $requestStack,
     #[Autowire('%env(GOOGLE_RECAPTCHA_SECRET_KEY)%')] private string $recaptchaSecretKey) {}

     public function authenticate(Request $request): Passport
     {
         $recaptchaResponse = $request->request->get('g-recaptcha-response');
     
         // If reCAPTCHA response is provided, verify it
         if (!empty($recaptchaResponse)) {
             $recaptcha = new ReCaptcha($this->recaptchaSecretKey);
             $resp = $recaptcha->verify($recaptchaResponse, $request->getClientIp());
     
             if (!$resp->isSuccess()) {
                 // If reCAPTCHA is provided but invalid, show an error and deny authentication
                 $session = $this->requestStack->getSession();
                 $session->getFlashBag()->add('error', 'Le reCAPTCHA est invalide.');
     
                 return new Passport(
                     new UserBadge(''), // Prevent authentication
                     new PasswordCredentials(''), 
                     []
                 );
             }
         }
     
         // Proceed with normal authentication if reCAPTCHA is either valid or absent
         $email = $request->getPayload()->getString('email');
         $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);
     
         return new Passport(
             new UserBadge($email),
             new PasswordCredentials($request->getPayload()->getString('password')),
             [
                 new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
             ]
         );
     }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        $user = $token->getUser();
        // Vérifie si l'utilisateur a le rôle 'ROLE_CLIENT' et si l'email n'est pas vérifié
        if (in_array('ROLE_CLIENT', $user->getRoles()) && !$user->isVerified()) {
            // Redirige avec un message flash d'erreur
            return new RedirectResponse(
                $this->urlGenerator->generate(self::LOGIN_ROUTE, [
                    'error' => 'Données invalides. Veuillez confirmer votre compte avant de vous connecter.'
                ])
            );
        }

         // Vérifie si l'utilisateur est banni
         if (in_array('ROLE_CLIENT', $user->getRoles()) && $user->getIsBanned()) {
            // Déconnecte l'utilisateur
            $this->security->logout(false);

            // Ajoute un message flash pour l'alerte
            $request->getSession()->getFlashBag()->add('error', 'Votre compte est banni,vous recevez un mail lorsque votre compte sera actif.');

            // Redirige vers la page de connexion
            return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
        }

        // Redirection en fonction des rôles
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
        } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
        } else {
            return new RedirectResponse($this->urlGenerator->generate('app_dashboard_client'));
        }
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}