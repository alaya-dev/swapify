<?php
namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;


class AdminController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    

    public function __construct(EntityManagerInterface $em , UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/admin/add_admin', name: 'add_admin')]
    public function addAdmin(Request $request)
{

    if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
        throw $this->createAccessDeniedException('Accès refusé.');
    }

    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Attribuer le rôle ROLE_ADMIN directement ici
        $user->setRoles(['ROLE_ADMIN']); // Assurer que l'utilisateur a bien ce rôle


        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $form->get('password')->getData() // Obtient le mot de passe
        ) ;
        $user->setPassword($hashedPassword);


        // Enregistrer l'utilisateur dans la base de données
        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Admin ajouté avec succès');
        return $this->redirectToRoute('admin_list');
    }

    return $this->render('admin/add_admin.html.twig', [
        'form' => $form->createView(),
    ]);
}



#[Route('/admin/liste', name: 'admin_list')]
public function listAdmins(UserRepository $userRepository): Response
{
    // Requête pour récupérer tous les utilisateurs avec le rôle 'ROLE_ADMIN'
    $admins = $userRepository->findAdmins();

    // Rendu du template avec la liste des administrateurs
    return $this->render('admin/liste_admin.html.twig', [
        'admins' => $admins,
    ]);
}

    #[Route('/admin/supprimer/{id}', name: 'admin_delete')]
    public function deleteAdmin(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur
        $user = $entityManager->getRepository(User::class)->find($id);

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Administrateur supprimé avec succès.');
        return $this->redirectToRoute('admin_list');
    }




    #[Route('/client/liste', name: 'client_list')]
    public function listClients(UserRepository $userRepository): Response
    {
        // Requête pour récupérer tous les utilisateurs avec le rôle 'ROLE_CLIENT'
        $clients = $userRepository->findClients();

        // Rendu du template avec la liste des clients
        return $this->render('client/liste_client.html.twig', [
            'clients' => $clients,
        ]);
    }


    #[Route('/client/supprimer/{id}', name: 'client_delete')]
    public function deleteClient(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur
        $user = $entityManager->getRepository(User::class)->find($id);

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Le client est supprimé avec succès.');
        return $this->redirectToRoute('client_list');
    }



    #[Route('/admin/edit/{id}', name: 'admin_edit')]
    public function editAdmin(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
    $admin = $userRepository->find($id);

    $form = $this->createForm(UserType::class, $admin); // Utilise le formulaire existant ou un nouveau pour l'admin
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush(); // Enregistre les modifications dans la base de données
        $this->addFlash('success', 'Admin modifié avec succès.');

        return $this->redirectToRoute('admin_list'); // Redirection vers la liste ou une autre page
    }

    return $this->render('admin/edit.html.twig', [
        'form' => $form->createView(),
        'admin' => $admin,
    ]);
    }


    #[Route('/profil', name: 'app_profile')]
    public function profile(): Response
    {
    $user = $this->getUser(); // Récupérer l'utilisateur connecté
    if (!$user) {
        return $this->redirectToRoute('app_login'); // Rediriger vers login si non connecté
    }

    return $this->render('admin/edit.html.twig', [
        'user' => $user,
    ]);
    }

}
