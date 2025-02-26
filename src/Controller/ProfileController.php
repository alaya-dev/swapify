<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError; 

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        // Créer le formulaire pour l'édition du profil
        $form = $this->createForm(ProfileType::class, $user);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifier si les champs nom, prénom et téléphone sont vides
            if (empty($user->getNom())) {
                $form->get('nom')->addError(new FormError('Le nom ne peut pas être vide.'));
            }

            if (empty($user->getPrenom())) {
                $form->get('prenom')->addError(new FormError('Le prénom ne peut pas être vide.'));
            }

            if (empty($user->getTel())) {
                $form->get('tel')->addError(new FormError('Le numéro de téléphone ne peut pas être vide.'));
            }

            // Si le formulaire est valide, on le traite
            if ($form->isValid()) {
                // Vérifier si un mot de passe a été fourni et si ce n'est pas vide
                $newPassword = $form->get('password')->getData();
                if ($newPassword !== null && $newPassword !== '') {
                    // Hacher et enregistrer le mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                }

                // Sauvegarder les modifications en base de données
                $entityManager->flush();

                // Message de succès
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

                // Rediriger vers la page du profil
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}