<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categorie')]
final class CategorieController extends AbstractController
{


    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')" )]
    #[Route(name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

 
    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,#[Autowire('%uploads_directory%')] string $photoDir): Response
    {
        $user = $this->getUser();
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $libelle = $categorie->getLibelle();
            
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $destination = $photoDir . DIRECTORY_SEPARATOR . $fileName;
                copy($photo->getPathname(), $destination);
            }
            $categorie->setImage($fileName);
            $existingCategorie = $entityManager->getRepository(Categorie::class)
                ->findOneBy(['libelle' => $libelle]);


            if ($existingCategorie) {
                $this->addFlash('warning', 'Categorie existante !');
                if($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')){
                return $this->redirectToRoute('app_admin_categories');
                }
                return $this->redirectToRoute('app_annonce_new');

            }

            // Save the new category
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'Categorie ajoutée !');

            // Redirect based on user role
            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
                return $this->redirectToRoute('app_admin_categories');
            }

            return $this->redirectToRoute('app_annonce_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CLIENT')" )]
    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')" )]
    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager,#[Autowire('%uploads_directory%')] string $photoDir): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $destination = $photoDir . DIRECTORY_SEPARATOR . $fileName;
                copy($photo->getPathname(), $destination);
            }
            $categorie->setImage($fileName);
            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')" )]
    #[Route('/{id}/delete', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        // Redirect based on user role
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}