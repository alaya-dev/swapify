<?php

namespace App\Controller;

use App\Enum\EtatEnum;
use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route('/blogs', name: 'app_blog_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager,PaginatorInterface $paginator,Request $request): Response
    {
        $acceptedBlogs = $entityManager->getRepository(Blog::class)->findBy(
            ['statut' => EtatEnum::Acceptée],
            ['id' => 'DESC'] 
        );
        $blogs = $paginator->paginate(
            $acceptedBlogs, // Query or array
            $request->query->getInt('page', 1), // Page number
            4 // Items per page
        );
    
        return $this->render('blog/index.html.twig', [
            'blogs' => $blogs,
        ]);
    }
    #[Route('/all', name: 'app_blog_all', methods: ['GET'])]
    public function display(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
    
        if ($this->isGranted('ROLE_ADMIN') ||  $this->isGranted('ROLE_SUPER_ADMIN')) {
            // Fetch all blogs for admin
            $acceptedBlogs = $entityManager->getRepository(Blog::class)->findBy(
                ['statut' => 'Acceptée'],
                ['id' => 'DESC']
            );
    
            $pendingBlogs = $entityManager->getRepository(Blog::class)->findBy(
                ['statut' => 'enAttente'],
                ['id' => 'DESC']
            );
    
            $rejectedBlogs = $entityManager->getRepository(Blog::class)->findBy(
                ['statut' => 'Rejetée'],
                ['id' => 'DESC']
            );
    
            $blogs = array_merge($acceptedBlogs, $pendingBlogs, $rejectedBlogs);
    
            // Redirect to the admin Twig file
            return $this->render('blog/all_blogs.html.twig', [
                'blogs' => $blogs,
            ]);
        } elseif ($this->isGranted('ROLE_CLIENT')) {
            // Fetch only the client's blogs
            $blogs = $entityManager->getRepository(Blog::class)->findBy(
                ['user' => $user],
                ['id' => 'DESC']
            );
    
            // Redirect to the client Twig file
            return $this->render('blog/myblogs.html.twig', [
                'blogs' => $blogs,
            ]);
        }
    
        // Optionally, handle other roles or redirect to an error page
        return $this->redirectToRoute('app_home');
    }
    
    
    
    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $blog = new Blog();
        $blog->setUser($this->getUser());
           
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
        
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
        
                // Move the file to the directory where images are stored
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
        
                // Update the 'image' property to store the file name
                $blog->setImage($newFilename);
            }
        
            $entityManager->persist($blog);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_blog_all', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog, BlogRepository $blogRepository): Response
    {
        $user = $this->getUser();
        $blogs = $blogRepository->findBy(['user' => $user]);
        
        // Check if the user is an admin or super admin
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->render('blog/show_admin.html.twig', [
                'blog' => $blog,
            ]);
        }
    
        // Check if the user is a client
        if ($this->isGranted('ROLE_CLIENT')) {
            // If the client is viewing their own blog
            if ($blog->getUser() === $user) {
                return $this->render('blog/show_my_blog.html.twig', [
                    'blog' => $blog,
                    'blogs'=>$blogs
                ]);
            }
    
            // If the client is viewing another user's blog
            return $this->render('blog/show.html.twig', [
                'blog' => $blog,
                'all_blogs'=>$blogRepository->findAll(),
            ]);
        }
    
        // Redirect if the user is not authorized
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
            
        ]);
    }
    

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');  
        if ($this->isGranted('ROLE_ADMIN')) {
            $blog->setStatut(EtatEnum::Acceptée);
        } else {
            if ($blog->getUser() !== $this->getUser()) {
                throw $this->createAccessDeniedException('You are not allowed to edit this blog.');
            }
            $blog->setStatut(EtatEnum::enAttente);  
        }
    
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
                        /** @var UploadedFile $imageFile */
                        $imageFile = $form->get('imageFile')->getData();
        
                        if ($imageFile) {
                            $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    
                            // Move the file to the directory where images are stored
                            $imageFile->move(
                                $this->getParameter('uploads_directory'),
                                $newFilename
                            );
                    
                            // Update the 'image' property to store the file name
                            $blog->setImage($newFilename);
                        }
            $entityManager->flush();
    
            $this->addFlash('success', 'Blog updated successfully!');
            return $this->redirectToRoute('app_blog_all');
        }
        
        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }
    


    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($blog->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to delete this blog.');
        }
    
        if ($this->isCsrfTokenValid('delete' . $blog->getId(), $request->getPayload()->getString('_token'))) {
            // Remove related comments first
            foreach ($blog->getListeCommentaires() as $comment) {
                $entityManager->remove($comment);
            }
    
            // Remove the blog
            $entityManager->remove($blog);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_blog_all', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/admin/pending-blogs', name: 'admin_pending_blogs')]
    public function showPendingBlogs(BlogRepository $blogRepository)
    {
        // Find all blogs with the status "enAttente"
        $blogs = $blogRepository->findBy(['statut' => EtatEnum::enAttente]);

        return $this->render('blog/pending_blogs.html.twig', [
            'blogs' => $blogs,
        ]);
    }
    #[Route('/accept/{id}', name: 'accept_blog', methods: ['GET'])]

    public function acceptBlog(int $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Find the blog by its ID
        $blog = $entityManager->getRepository(Blog::class)->find($id);
        
        if (!$blog) {
            // If the blog is not found, show an error message
            $this->addFlash('error', 'Blog not found!');
            return $this->redirectToRoute('admin_pending_blogs');
        }
        
        // Check if the blog is already accepted
        if ($blog->getStatut() === EtatEnum::Acceptée) {
            $this->addFlash('info', 'This blog is already accepted.');
            return $this->redirectToRoute('admin_pending_blogs'); // Redirect to index to show flash message
        }
        
        // Update the blog's statut to 'Acceptée'
        $blog->setStatut(EtatEnum::Acceptée);
        $entityManager->flush();
        
        // Add a success message to the flash bag
        $this->addFlash('success', 'Blog accepted successfully!');
        
        // Redirect back to the index page
        return $this->redirectToRoute('admin_pending_blogs');
    }
    #[Route('/reject/{id}', name: 'reject_blog', methods: ['GET'])]
public function rejectBlog(int $id, EntityManagerInterface $entityManager): RedirectResponse
{
    // Find the blog by its ID
    $blog = $entityManager->getRepository(Blog::class)->find($id);
    
    if (!$blog) {
        // If the blog is not found, show an error message
        $this->addFlash('error', 'Blog not found!');
        return $this->redirectToRoute('admin_pending_blogs');
    }
    
    // Check if the blog is already rejected
    if ($blog->getStatut() === EtatEnum::Rejetée) {
        $this->addFlash('info', 'This blog is already rejected.');
        return $this->redirectToRoute('admin_pending_blogs'); // Redirect to index to show flash message
    }
    
    // Update the blog's statut to 'Rejetée'
    $blog->setStatut(EtatEnum::Rejetée);
    $entityManager->flush();
    
    // Add a success message to the flash bag
    $this->addFlash('success', 'Blog rejected successfully!');
    
    // Redirect back to the index page
    return $this->redirectToRoute('admin_pending_blogs');
}

#[Route('/rate/{id}', name: 'app_blog_rate', methods: ['POST'])]
public function rateBlog(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');
    $user = $this->getUser();

    // Check if the user has already rated this blog
    if ($blog->hasUserRated($user)) {
        $this->addFlash('error', 'You have already rated this blog.');
        return $this->redirectToRoute('app_blog_index');
    }

    $rating = (int) $request->request->get('rating');

    // Validate the rating
    if ($rating < 0 || $rating > 5) {
        $this->addFlash('error', 'Invalid rating. Please select a value between 0 and 5.');
        return $this->redirectToRoute('app_blog_index');
    }

    // Add the rating
    $blog->addRate($rating);
    $blog->addRatedByUser($user); // Mark the user as having rated this blog
    $entityManager->flush();

    $this->addFlash('success', 'Rating added successfully!');
    return $this->redirectToRoute('app_blog_index');
}


#[Route('/my-blogs', name: 'app_user_blogs', methods: ['GET'])]
public function displayUserBlogs(BlogRepository $blogRepository,Request $request): Response
{
   

            $filter = $request->query->get('filter', 'all');
            $blogs = match ($filter) {
                'pending' => $blogRepository->findBy(['statut' => 'enAttente', 'user' => $this->getUser()]),
                'active' => $blogRepository->findBy(['statut' => 'Acceptée', 'user' => $this->getUser()]),
                'inactive' => $blogRepository->findBy(['statut' => 'Rejetée', 'user' => $this->getUser()]),
                default => $blogRepository->findBy(['user' => $this->getUser()]),
            };
            
    
            return $this->render('blog/my_blogs.html.twig', [
                'blogs' => $blogs,
                
            ]);
        }
    
// In BlogController.php
#[Route('/stats', name: 'app_blog_stats', methods: ['GET'])]
public function blogStats(EntityManagerInterface $entityManager): Response
{
    // Get the total number of blogs
    $totalBlogs = $entityManager->getRepository(Blog::class)->count([]);

    // Get the total number of comments
    $totalComments = $entityManager->createQueryBuilder()
        ->select('COUNT(c.id)')
        ->from('App\Entity\Commentaire', 'c')
        ->getQuery()
        ->getSingleScalarResult();

    // Get the total number of rates
    $totalRates = $entityManager->createQueryBuilder()
        ->select('SUM(b.totalRates)') // Assuming 'totalRates' stores the sum of ratings
        ->from('App\Entity\Blog', 'b')
        ->getQuery()
        ->getSingleScalarResult() ?? 0;

    return $this->render('blog/stats.html.twig', [
        'totalBlogs' => $totalBlogs,
        'totalComments' => $totalComments,
        'totalRates' => $totalRates,
    ]);
}

}


