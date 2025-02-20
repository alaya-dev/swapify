<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Entity\User;
use App\Form\LivraisonType;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use App\Repository\LivraisonRepository;
use App\Repository\LivreurRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LivraisonController extends AbstractController
{
   


    #[Route('/livraison/success', name: 'livraison_success')]
    public function success(): Response
    {
        return new Response("<h1>Livraison créée avec succès !</h1>");
    }

    

    #[Route('/dashboard/client/livraison', name: 'livraison_list')]
    public function listLivraisons(LivraisonRepository $livraisonRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $livraisons = $livraisonRepository->findByUser($user);

        return $this->render('livraison/livraison_liste_user.html.twig', [
            'livraisons' => $livraisons,
        ]);
    }
    #[Route('/liste_livraisons', name: 'liste_livraison')]
    public function AfficherLivraisons(LivraisonRepository $livraisonRepository, LivreurRepository $livreurRepo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }


        $livraisons = $livraisonRepository->findAll();
        $livreurs = $livreurRepo->findAll();
        return $this->render('livraison/liste_livraison_admin.html.twig', [
            'livraisons' => $livraisons,
            'livreurs' => $livreurs
        ]);
    }
    #[Route('/livraison/{id}/qr', name: 'livraison_qr')]
    public function generateQrCode(Livraison $livraison): Response
    {
        $url = 'http://192.168.37.161:8000/livraison/' . $livraison->getId() . '/confirmer';

        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->size(200)
            ->margin(10)
            ->build();

        return new Response($qrCode->getString(), 200, ['Content-Type' => 'image/png']);
    }


    #[Route('/livraison/{id}/confirmer', name: 'livraison_confirmer', methods: ['GET'])]
    public function confirmerLivraison(Livraison $livraison, EntityManagerInterface $em): Response
    {
        $livraison->setStatut('Livrée');
        $em->persist($livraison);
        $em->flush();

        return $this->render('livraison/confirmation.html.twig', [
            'livraison' => $livraison
        ]);
    }
    #[Route('/livraison/supprimer/{id}', name: 'livraison_delete')]
    public function deleteAdmin(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur
        $livraison = $entityManager->getRepository(Livraison::class)->find($id);

        // Supprimer l'utilisateur
        $entityManager->remove($livraison);
        $entityManager->flush();

        $this->addFlash('success', 'livraison supprimé.');
        return $this->redirectToRoute('liste_livraison');
    }
    #[Route('/livraison/assign/{livraisonId}/{livreurId}', name: 'assign_livreur', methods: ['POST'])]
    public function assignLivreur(int $livraisonId, int $livreurId, LivraisonRepository $livraisonRepo, LivreurRepository $livreurRepo, EntityManagerInterface $entityManager): JsonResponse
    {
        $livraison = $livraisonRepo->find($livraisonId);
        $livreur = $livreurRepo->find($livreurId);

        if (!$livraison || !$livreur) {
            return new JsonResponse(['success' => false, 'message' => 'Livraison ou livreur introuvable'], 400);
        }

        $livraison->setLivreur($livreur);
        $livraison->setStatut('En cours de livraison');
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
    #[Route('/livraison/new', name: 'livraison_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $livraison = new Livraison();
        $livraison->setIdExpediteur($this->getUser()); // Associer l'expéditeur connecté
        $livraison->setStatut('En attente de localisation du destinataire');
        $livraison->setDate(new \DateTime());
        $livraison->setPaymentExp('non payé');
        $form = $this->createForm(LivraisonType::class, $livraison);

        // Supprimer les champs inutiles pour l'expéditeur
        $form->remove('livreur');
        $form->remove('localisation_destinataire_lat');
        $form->remove('localisation_destinataire_lng');
        $form->remove('TelephoneDestinataire');
        $form->remove('CodePostalDestinataire');
        $form->remove('payment_exp');
        $form->remove('payment_dist');
        $form->remove('statut');
        $form->remove('date');
        $form->remove('adresseDestiniataire');



        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($livraison);
            $em->flush();

            return $this->redirectToRoute('payment_page', ['id' => $livraison->getId()]);
        }

        return $this->render('livraison/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/livraison/LivDestinataire/{id}', name: 'livraison_dist')]
    public function createLivDestinataire(Request $request, EntityManagerInterface $em, Livraison $livraison): Response
    {
        // Vérifier si l'utilisateur est bien le destinataire
        

        $form = $this->createForm(LivraisonType::class, $livraison);

        // Supprimer les champs inutiles pour le destinataire
        $form->remove('id_expediteur');
        $form->remove('id_distinataire');
        $form->remove('livreur');
        $form->remove('localisation_expediteur_lat');
        $form->remove('localisation_expediteur_lng');
        $form->remove('TelephoneExpediteur');
        $form->remove('CodePostalExpediteur');
        $form->remove('adresseExpediteur');
        $form->remove('payment_exp');
        $form->remove('payment_dist');
        $form->remove('statut');
        $form->remove('date');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $livraison->setStatut('En cours de traitement');
            $livraison->setPaymentDist('non payé');
            $em->flush();
            return $this->redirectToRoute('payment_page', ['id' => $livraison->getId()]);
        }

        return $this->render('livraison/form_destinataire.html.twig', [
            'form' => $form->createView(),
            'livraison' => $livraison
        ]);
    }
    #[Route('/livraison/editLivDestinataire/{id}', name: 'edit_livraison_dist')]
    public function editLivDestinataire(Request $request, EntityManagerInterface $em, Livraison $livraison): Response
    {
        // Vérifier si l'utilisateur est bien le destinataire
        

        $form = $this->createForm(LivraisonType::class, $livraison);

        // Supprimer les champs inutiles pour le destinataire
        $form->remove('id_expediteur');
        $form->remove('id_distinataire');
        $form->remove('livreur');
        $form->remove('localisation_expediteur_lat');
        $form->remove('localisation_expediteur_lng');
        $form->remove('TelephoneExpediteur');
        $form->remove('CodePostalExpediteur');
        $form->remove('adresseExpediteur');

        $form->remove('payment_exp');
        $form->remove('payment_dist');
        $form->remove('statut');
        $form->remove('date');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('livraison_list');
        }

        return $this->render('livraison/edit_destinataire_livraison.html.twig', [
            'form' => $form->createView(),
            'livraison' => $livraison
        ]);
    }
    
    #[Route('/livraison/editLivExpidataire/{id}', name: 'livraison_edit_expidataire')]
    public function updateLivDestinataire(Request $request, EntityManagerInterface $em, Livraison $livraison): Response
    {
        // Vérifier si l'utilisateur est bien le destinataire
       
        $form = $this->createForm(LivraisonType::class, $livraison);

        // Supprimer les champs inutiles pour le destinataire
        $form->remove('id_expediteur');
        $form->remove('id_distinataire');
        $form->remove('livreur');
        $form->remove('localisation_destinataire_lat');
        $form->remove('localisation_destinataire_lng');
        $form->remove('TelephoneDestinataire');
        $form->remove('CodePostalDestinataire');
        $form->remove('payment_exp');
        $form->remove('payment_dist');
        $form->remove('adresseDestiniataire');

        $form->remove('statut');
        $form->remove('date');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $em->flush();
            return $this->redirectToRoute('livraison_list');
        }

        return $this->render('livraison/edit_expiditeur_livraison.html.twig', [
            'form' => $form->createView(),
            'livraison' => $livraison
        ]);
    }
    #[Route('/livraison/annuler/{id}', name: 'livraison_annuler')]
    public function AnnulerLiv(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur
        $livraison = $entityManager->getRepository(Livraison::class)->find($id);

        // Supprimer l'utilisateur
        $entityManager->remove($livraison);
        $entityManager->flush();

        $this->addFlash('success', 'livraison annulé.');
        return $this->redirectToRoute('livraison_list');
    }
    #[Route('/livraison', name: 'livraison')]
    public function index(): Response
    {
        return $this->render('livraison/livraison.html.twig', [
            'controller_name' => 'LivraisonController',
        ]);
    }

}
