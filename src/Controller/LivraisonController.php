<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Entity\User;
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
    #[Route('/livraison/create', name: 'livraison_create', methods: ['GET', 'POST'])]
    public function createLivraison(Request $request, EntityManagerInterface $em, UserRepository $userRepository, ValidatorInterface $validator): Response
    {
        $expediteur = $this->getUser();
        if (!$expediteur) {
            return $this->redirectToRoute('app_login');
        }
        $emailDestinataire = $request->request->get('email_destinataire');
        $destinataire = $userRepository->findOneBy(['email' => $emailDestinataire]);
        if (!$destinataire) {
            $this->addFlash('error', 'Destinataire introuvable.');
            return $this->redirectToRoute('livraison_form');
        }



        $livraison = new Livraison();
        $livraison->setIdExpediteur($expediteur);
        $livraison->setIdDistinataire($destinataire);

        $livraison->setTelephoneExpediteur($request->request->get('telephone_expediteur'));
        $livraison->setCodePostalExpediteur($request->request->get('code_postal_expediteur'));

        $livraison->setLocalisationExpediteurLat($request->request->get('localisation_expediteur_lat'));
        $livraison->setLocalisationExpediteurLng($request->request->get('localisation_expediteur_lng'));
        $livraison->setStatut('En attente de localisation du destinataire');
        $livraison->setPaymentExp('non payé');
        $livraison->setDate(new \DateTime());


        $em->persist($livraison);
        $em->flush();

        $this->addFlash('success', 'Livraison créée. En attente de localisation du destinataire.');
        return $this->redirectToRoute('payment_page', ['id' => $livraison->getId()]);
    }



    #[Route('/livraison/success', name: 'livraison_success')]
    public function success(): Response
    {
        return new Response("<h1>Livraison créée avec succès !</h1>");
    }

    #[Route('/livraison/CreateLivDist/{id}', name: 'livraison_create_dist')]
    public function LivraisonExp(Livraison $livraison, Request $request, EntityManagerInterface $em): Response
    {



        if ($request->isMethod('POST')) {
            $livraison->setTelephoneDestinataire($request->request->get('telephone_destinataire'));
            $livraison->setCodePostalDestinataire($request->request->get('code_postal_destinataire'));
            $livraison->setLocalisationDestinataireLat($request->request->get('localisation_destinataire_lat'));
            $livraison->setLocalisationDestinataireLng($request->request->get('localisation_destinataire_lng'));
            $livraison->setPaymentDist('non payé');
            $livraison->setStatut('En cours de traitement');

            $em->flush();

            $this->addFlash('success', 'Localisation ajoutée avec succès.');
            return $this->redirectToRoute('payment_page', ['id' => $livraison->getId()]);
        }

        return $this->render('livraison/livraison_edit.html.twig', [
            'livraison' => $livraison
        ]);
    }

    #[Route('/livraison/editLivDes/{id}', name: 'livraison_edit_destinataire')]
    public function editLivraison(Livraison $livraison, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier si l'objet livraison existe
        if (!$livraison) {
            throw $this->createNotFoundException("La livraison demandée n'existe pas.");
        }

        if ($request->isMethod('POST')) {
            $livraison->setCodePostalDestinataire($request->request->get('code_postal_destinataire'));
            $livraison->setTelephoneDestinataire($request->request->get('telephone_destinataire'));
            $livraison->setLocalisationDestinataireLat($request->request->get('localisation_destinataire_lat'));
            $livraison->setLocalisationDestinataireLng($request->request->get('localisation_destinataire_lng'));

            $em->flush();

            $this->addFlash('success', 'Localisation mise à jour avec succès.');

            // Rediriger vers la liste des livraisons (assure-toi que cette route existe)
            return $this->redirectToRoute('livraison_list');
        }

        return $this->render('livraison/livraison_edit.html.twig', [
            'livraison' => $livraison
        ]);
    }
    #[Route('/livraison/editLivExp/{id}', name: 'livraison_edit_expediteur')]
    public function editLivraisonExpediteur(Livraison $livraison, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier si l'objet livraison existe
        if (!$livraison) {
            throw $this->createNotFoundException("La livraison demandée n'existe pas.");
        }

        if ($request->isMethod('POST')) {
            $livraison->setCodePostalExpediteur($request->request->get('code_postal_expediteur'));
            $livraison->setTelephoneExpediteur($request->request->get('telephone_expediteur'));
            $livraison->setLocalisationExpediteurLat($request->request->get('localisation_expediteur_lat'));
            $livraison->setLocalisationExpediteurLng($request->request->get('localisation_expediteur_lng'));

            $em->flush();

            $this->addFlash('success', 'Localisation mise à jour avec succès.');

            // Rediriger vers la liste des livraisons (assure-toi que cette route existe)
            return $this->redirectToRoute('livraison_list');
        }

        return $this->render('livraison/edit_exp_livraison.html.twig', [
            'livraison' => $livraison
        ]);
    }


    #[Route('/livraison/new', name: 'livraison_form')]
    public function showForm(): Response
    {
        return $this->render('livraison/livraison_form.html.twig');
    }

    #[Route('/livraisons', name: 'livraison_list')]
    public function listLivraisons(LivraisonRepository $livraisonRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $livraisons = $livraisonRepository->findByUser($user);

        return $this->render('livraison/list.html.twig', [
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
}
