<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Form\LivraisonType;
use Endroid\QrCode\Writer\PngWriter;
use App\Repository\LivraisonRepository;
use App\Repository\LivreurRepository;
use App\Repository\UserRepository;
use App\Service\ETAService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class LivraisonController extends AbstractController
{



    #[Route('/livraison/success', name: 'livraison_success')]
    public function success(): Response
    {
        return new Response("<h1>Livraison créée avec succès !</h1>");
    }



    #[Route('/dashboard/client/livraison', name: 'livraison_list')]
    public function listLivraisons(LivraisonRepository $livraisonRepository,ETAService $etaService): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        


        $livraisons = $livraisonRepository->findByUser($user);
        foreach ($livraisons as $livraison) {
            $latExpediteur = $livraison->getLocalisationExpediteurLat();
            $lonExpediteur = $livraison->getLocalisationExpediteurLng();
            $latDestinataire = $livraison->getLocalisationDestinataireLat();
            $lonDestinataire = $livraison->getLocalisationDestinataireLng();
        
            // Vérifier si une valeur est nulle
            if ($latExpediteur === null || $lonExpediteur === null || $latDestinataire === null || $lonDestinataire === null) {
                continue; // Ignore cette livraison
            }
        
            // Calcul de l'ETA
            $eta = $etaService->calculerETA($latExpediteur, $lonExpediteur, $latDestinataire, $lonDestinataire);
        
            // Ajouter l'ETA à l'objet livraison
            $livraison->eta = $eta;
        }
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
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('error403'); // Remplace 'app_home' par ta route
        }



        $livraisons = $livraisonRepository->findAll();

        $livreurs = $livreurRepo->findAll();
        return $this->render('livraison/liste_livraison_admin.html.twig', [
            'livraisons' => $livraisons,
            'livreurs' => $livreurs
        ]);
    }
    #[Route('/livraison/{id}/qr/destinataire', name: 'livraison_qr_destinataire')]
    public function generateQrCode(Livraison $livraison): Response
    {
        $url = 'http://192.168.189.44:8000/livraison/' . $livraison->getId() . '/confirmer/destinataire';

        $qrCode = new QrCode($url);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }
    #[Route('/livraison/{id}/qr/expediteur', name: 'livraison_qr_expediteur')]
    public function generateQrCode2(Livraison $livraison): Response
    {
        $url = 'http://192.168.189.44:8000/livraison/' . $livraison->getId() . '/confirmer/expediteur';

        $qrCode = new QrCode($url);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }
    
    #[Route('/livraison/{id}/confirmer/expediteur', name: 'livraison_confirmer_expediteur', methods: ['GET'])]
    public function confirmerLivraison2(Livraison $livraison, EntityManagerInterface $em): Response
    {

        if ($livraison->getStatut() === 'En cours de livraison') {
            $livraison->setStatut('Livrée pour l\'expéditeur');
        } elseif ($livraison->getStatut() === 'Livrée pour le destinataire') {
            $livraison->setStatut('Livrée pour les deux');
        }
        $em->persist($livraison);
        $em->flush();

        return $this->render('livraison/confirmation.html.twig', [
            'livraison' => $livraison
        ]);
    }

    #[Route('/livraison/{id}/confirmer/destinataire', name: 'livraison_confirmer_destinataire', methods: ['GET'])]
    public function confirmerLivraison(Livraison $livraison, EntityManagerInterface $em): Response
    {


        if ($livraison->getStatut() === 'En cours de livraison') {
            $livraison->setStatut('Livrée pour le destinataire');
        } elseif ($livraison->getStatut() === 'Livrée pour l\'expéditeur') {
            $livraison->setStatut('Livrée pour les deux');
        }
        $em->persist($livraison);
        $em->flush();

        return $this->render('livraison/confirmation.html.twig', [
            'livraison' => $livraison
        ]);
    }
    #[Route('/livraison/supprimer/{id}', name: 'livraison_delete')]
    public function deleteAdmin(int $id, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('error403'); // Remplace 'app_home' par ta route
        }
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


    #[Route('/livraison/new/{id}', name: 'livraison_create', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $em, $id, UserRepository $ur): Response
    {

        $userDestinataire = $ur->find($id);
        $livraison = new Livraison();
        $livraison->setIdExpediteur($this->getUser()); // Associer l'expéditeur connecté
        $livraison->setStatut('En attente de localisation du destinataire');
        $livraison->setDate(new \DateTime());
        $livraison->setIdDistinataire($userDestinataire);
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
