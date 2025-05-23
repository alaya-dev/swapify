<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\BlogRepository;
use App\Repository\CommentaireRepository;
use App\Service\BadWordFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/commentaire/new/{blogId}', name: 'app_commentaire_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $blogId, BlogRepository $blogRepository, BadWordFilter $badWordFilter): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER'); // Ensure only logged-in users can comment
    
        $blog = $blogRepository->find($blogId);
        if (!$blog) {
            throw $this->createNotFoundException("Blog not found.");
        }
    
        $commentaire = new Commentaire();
        $commentaire->setContenuCmt($request->request->get('contenu'));
        $commentaire->setBlog($blog);
        $commentaire->setUser($this->getUser()); // Associate the comment with the logged-in user
    
            // Check for bad words in the comment content
        if ($badWordFilter->containsBadWords($commentaire->getContenuCmt())) {
            $this->addFlash('error', 'Your comment contains inappropriate language and cannot be published.');
            return $this->redirectToRoute('app_blog_show', ['id' => $blogId]);
        }
        $entityManager->persist($commentaire);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_blog_show', ['id' => $blogId]);
    }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        // Ensure only the comment owner can access the edit page
        if ($commentaire->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this comment.');
        }
    
        if ($request->isXmlHttpRequest()) {
            // Handle AJAX request
            $newContent = $request->request->get('contenu');
            $commentaire->setContenuCmt($newContent);
            $entityManager->flush();
    
            return $this->json([
                'success' => true,
                'newContent' => $newContent,
            ]);
        }
    
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
{
    // Ensure only the comment owner or an admin can delete the comment
    $isCommentOwner = $commentaire->getUser() === $this->getUser();
    $isAdmin = $this->isGranted('ROLE_ADMIN');
    
    // Allow an admin to delete any comment, and the owner can delete their own comment
    if (!$isCommentOwner && !$isAdmin) {
        throw $this->createAccessDeniedException('You are not allowed to delete this comment.');
    }

    // Validate CSRF token
    if ($this->isCsrfTokenValid('delete' . $commentaire->getId(), $request->get('_token'))) {
        $entityManager->remove($commentaire);
        $entityManager->flush();
    }

    // Redirect to the blog's detail page after deletion
    return $this->redirectToRoute('app_blog_show', ['id' => $commentaire->getBlog()->getId()], Response::HTTP_SEE_OTHER);
}

}
