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

class ProfileClientController extends AbstractController
{

    
#[Route('/dashboard/client', name: 'app_client_profile')]
public function editProfile(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): Response {
    $user = $this->getUser();
    $form = $this->createForm(ProfileType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // Gestion de l'image
                /** @var UploadedFile|null $imageFile */
                $imageFile = $form->get('imageUrl')->getData();
                if ($imageFile) {
                    $imageFileName = md5(uniqid()) . '.' . $imageFile->guessExtension();
                    try {
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $imageFileName
                        );
                        $user->setImageUrl('/uploads/images/' . $imageFileName);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l’image : ' . $e->getMessage());
                        return $this->render('dashboard/accueil_client.twig', [
                            'form' => $form->createView(),
                        ]);
                    }
                }

        $newPassword = $form->get('password')->getData();
        
        if (!empty($newPassword)) {
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

        return $this->redirectToRoute('app_dashboard_client');
    }

    return $this->render('dashboard/accueil_client.html.twig', [
        'form' => $form->createView(),
    ]);
}
    }