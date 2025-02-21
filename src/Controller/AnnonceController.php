<?php

namespace App\Controller;


use App\Entity\Annonce;
use App\Entity\Image;
use App\Entity\Position;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\etat;
use App\Form\AnnoncesFilterType;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/annonce')]
final class AnnonceController extends AbstractController
{


    #[Route(name: 'app_annonce_index', methods: ['GET', 'POST'])]
    public function index(Request $request, AnnonceRepository $annonceRepository, ImageRepository $imageRepository)
    {
        $form = $this->createForm(AnnoncesFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_annonce_index', [
                'titre' => $form->get('titre')->getData(),
                'categorie' => $form->get('categorie')->getData(),
                'Region' => $form->get('Region')->getData(),
                'dateCreation' => $form->get('dateCreation')->getData(),
            ]);
        }

        $annonces = $this->handleFilter($request, $annonceRepository);
        $images = $imageRepository->findAll();
        return $this->render('annonce/listAnnonces.html.twig', [
            'form' => $form->createView(),
            'annonces' => $annonces,
            'images' => $images,
        ]);
    }

    #[Route('/mesAnnonces', name: 'mesAnnonces', methods: ['GET', 'POST'])]
    public function mesAnnonces(AnnonceRepository $annonceRepository, Request $request): Response
    {
        $filter = $request->query->get('filter', 'all');

        $annonces = match ($filter) {
            'pending' => $annonceRepository->findBy(['statut' => 'En Attente', 'user' => $this->getUser()]),
            'active' => $annonceRepository->findBy(['statut' => 'Acceptée', 'user' => $this->getUser()]),
            'inactive' => $annonceRepository->findBy(['statut' => 'Rejetée', 'user' => $this->getUser()]),
            default => $annonceRepository->findBy(['user' => $this->getUser()]),
        };

        return $this->render('annonce/mesAnnonces.html.twig', [
            'annonces' => $annonces,
        ]);
    }


    private function handleFilter(Request $request, AnnonceRepository $annonceRepository)
    {
        $titre = $request->query->get('titre');
        $cat = $request->query->get('categorie');
        $region = $request->query->get('Region');
        $date = $request->query->get('dateCreation');

        if ($titre || $cat || $region || $date) {
            return $annonceRepository->findFilteredAnnonces($titre, $cat, $region, $date);
        }

        return $annonceRepository->findValiderAnnonces();
    }



    #[Route('/new', name: 'app_annonce_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,  SluggerInterface $slugger, UserRepository $userRepo): Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Le champ titre est obligatoire !');
            return $this->redirectToRoute('app_annonce_new', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() &&  $form->isValid()) {

            /** @var UploadedFile[] $imageFiles */
            $imageFiles = $form->get('imageFile')->getData();
            foreach ($imageFiles as $imageFile) {
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);

                    $image = new Image();
                    $image->setImageName($newFilename);
                    $annonce->addImage($image);
                }
            }




            // Get localisation values
            $localisationX = $form->get('localisation_x')->getData();
            $localisationY = $form->get('localisation_y')->getData();

            // Set default values
            $annonce->setDisponibilite(true);
            $annonce->setStatut((etat::EnAttente)->label());
            $annonce->setX($localisationX);
            $annonce->setY($localisationY);



            $annonce->setUser($this->getUser());


            $entityManager->persist($annonce);
            $entityManager->flush();
            $this->addFlash('success', 'Demande d\'annonce envoyée !');


            return $this->redirectToRoute('mesAnnonces', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('annonce/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_annonce_show', methods: ['GET'])]
    public function show(Annonce $annonce, ImageRepository $imageRepository, AnnonceRepository $annonceRepository): Response
    {
        $images = $imageRepository->findAll();
        $annonces = $annonceRepository->findValideAnnoncesByUsrId($annonce->getUser());


        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
            'images' => $images,
            'annonces' => $annonces
        ]);
    }

    #[Route('/{id}/edit', name: 'app_annonce_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('mesAnnonces', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_annonce_delete', methods: ['POST'])]
    public function delete(Request $request, Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $annonce->getId(), $request->request->get('_token'))) {
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mesAnnonces', [], Response::HTTP_SEE_OTHER);
    }
}
