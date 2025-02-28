<?php

namespace App\Controller;


use App\Entity\Annonce;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\etat;
use App\Form\AnnoncesFilterType;
use App\Repository\FavorisRepository;
use App\Repository\ImageRepository;
use App\Repository\RatingRepository;
use App\Repository\UserRepository;
use App\Service\mailerMailJetService;
use App\Service\RecommandationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/annonce')]
final class AnnonceController extends AbstractController
{
    private $recommendationService;

    public function __construct(RecommandationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }


    #[Route(name: 'app_annonce_index', methods: ['GET', 'POST'])]
    public function index(Request $request, AnnonceRepository $annonceRepository, ImageRepository $imageRepository, FavorisRepository $favorisRepository)
    {

        $user = $this->getUser();

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

        $favoris = $user ? $favorisRepository->findBy(['user' => $user]) : [];


        return $this->render('annonce/listAnnonces.html.twig', [
            'form' => $form->createView(),
            'annonces' => $annonces,
            'images' => $images,
            'favoris' => array_map(fn($f) => $f->getAnnonces(), $favoris)
        ]);
    }


    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')")]
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


    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')")]
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
    public function show(Annonce $annonce, ImageRepository $imageRepository, AnnonceRepository $annonceRepository,  FavorisRepository $favorisRepository, RatingRepository $ratingRepo,$id): Response
    {

        $user = $this->getUser();


        $images = $imageRepository->findAll();
        $annonces = $annonceRepository->findValideAnnoncesByUsrId($annonce->getUser());

        $favoris = $user ? $favorisRepository->findBy(['user' => $user]) : [];


        $user2 = $annonce->getUser();
        $avgRating = $ratingRepo->getAverageRating($user2) ?? 0;


        //communication avec service recommandation déja crée
        $recommendations = $this->recommendationService->getRecommendations($id);
        //dd($recommendations);*
        $annonceIdsREC = array_column($recommendations, 'id');
        $annoncesSimilaires = $annonceRepository->findBy(['id' => $annonceIdsREC]);



        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
            'images' => $images,
            'annonces' => $annonces,
            'favoris' => array_map(fn($f) => $f->getAnnonces(), $favoris),
            'rating' => $avgRating,
            'annoncesSim'=> $annoncesSimilaires

        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')")]
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

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')")]
    #[Route('/{id}', name: 'app_annonce_delete', methods: ['POST'])]
    public function delete(Request $request, Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $annonce->getId(), $request->request->get('_token'))) {
            $entityManager->remove($annonce);
            $entityManager->flush();
        }
    
        // Check if the user has the role ADMIN or SUPER_ADMIN
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('app_admin_annonces_history', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->redirectToRoute('mesAnnonces', [], Response::HTTP_SEE_OTHER);
    }




     
}
