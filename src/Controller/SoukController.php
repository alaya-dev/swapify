<?php

namespace App\Controller;

use App\Entity\Souk;
use App\Form\SoukFormType;
use App\Repository\SoukRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/souk')]
final class SoukController extends AbstractController
{
    #[Route('/new', name: 'app_souk_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $souk = new Souk();
        $form = $this->createForm(SoukFormType::class, $souk);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($souk);
            $entityManager->flush();
            $this->addFlash('success', 'Souk ajouté avec succès.');
            return $this->redirectToRoute('app_souk_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');

        return $this->render('admin_souk/admin_souk.html.twig', [
            'souk' => $souk,
            'form' => $form,
        ]);
    }

    #[Route(name: 'app_souk_index', methods: ['GET'])]
    public function index(SoukRepository $soukRepository): Response
    {
        return $this->render('admin_souk/liste_souk.html.twig', [
            'souks' => $soukRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_souk_delete', methods: ['POST'])]
    public function delete(Request $request, Souk $souk, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $souk->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($souk);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_souk_index');
    }

    #[Route('/{id}/edit', name: 'app_souk_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Souk $souk, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SoukFormType::class, $souk);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_souk_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_souk/admin_souk.html.twig', [
            'souk' => $souk,
            'form' => $form,
        ]);
    }

    #[Route('/join/{id}', name: 'app_souk_join')]
    public function join(Souk $souk, EntityManagerInterface $entityManager, int $id): Response
    {
        foreach ($souk->getParticipant() as $user) {
            if ($user == $this->getUser()) {
                $this->addFlash('error', "you are already exist");
                return $this->redirectToRoute('souk_details', ["id" => $id], Response::HTTP_SEE_OTHER);
            }
        }
        $souk->addParticipant($this->getUser());
        $entityManager->persist($souk);
        $entityManager->flush();
        $this->addFlash('success', "You have successfully joined the souk.");
        return $this->redirectToRoute('souk_details', ["id" => $id], Response::HTTP_SEE_OTHER);
    }
}
