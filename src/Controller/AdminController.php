<?php
namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

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



    //gestion des annonces et catégories


    //#[IsGranted('ROLE_ADMIN')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/admin/annonces', name: 'app_admin_annonces')]
    public function index(AnnonceRepository $annonceRepository): Response
    {

        $annonces = $annonceRepository->findEnAttAnnonces();
      

        return $this->render('validationAnnonce/index.html.twig', [
            'annonces' => $annonces
        ]);
    }


    //#[IsGranted('ROLE_ADMIN')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function listCats(CategorieRepository $catRepository): Response
    {

        $cats = $catRepository->findAll();
      

        return $this->render('categorie/index.html.twig', [
            'categories' => $cats
        ]);
    }


    #[Route('/admin/annonce/{id}/validate', name: 'admin_annonce_validate')]
     //#[IsGranted('ROLE_ADMIN')]
     #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")]
    public function validate(Annonce $annonce, EntityManagerInterface $entityManager): RedirectResponse
    {
        $annonce->setStatut('Acceptee');
        $entityManager->flush();

        $this->addFlash('success', 'Annonce validated successfully!');
        return $this->redirectToRoute('app_admin_annonces');
    }


    #[Route('/admin/annonce/{id}/reject', name: 'admin_annonce_reject')]
    //#[IsGranted('ROLE_ADMIN')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")]
    public function reject(Annonce $annonce, EntityManagerInterface $entityManager): RedirectResponse
    {
        $annonce->setStatut('Rejetee');
        $entityManager->flush();

        $this->addFlash('success', 'Annonce rejected successfully!');
        return $this->redirectToRoute('app_admin_annonces');
    }


    #[Route('/admin/historique/annonces', name: 'app_admin_annonces_history')]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')")]
    public function histAnnonces(AnnonceRepository $annonceRepository, Request $request): Response
    {

    $filter = $request->query->get('filter', 'all');

    $annonces = match ($filter) {
        'pending' => $annonceRepository->findBy(['statut' => 'En Attente']),
        'active' => $annonceRepository->findBy(['statut' => 'Acceptée']),
        'inactive' => $annonceRepository->findBy(['statut' => 'Rejetée']),
        default => $annonceRepository->findAll(),
    };

    return $this->render('validationAnnonce/historiqueAnnonces.html.twig', [
        'annonces' => $annonces,
    ]);
    }




}
