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

        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->render('profile/profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    }
