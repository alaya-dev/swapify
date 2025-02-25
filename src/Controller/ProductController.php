<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Souk;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use App\Repository\SoukRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{

    #[Route('/product/new', name: 'app_product_new')]
    public function new(Request $request, SoukRepository $soukRepository, EntityManagerInterface $entityManager, #[Autowire('%photo_dir_products%')] string $photoDir): Response
    {
        $product = new Product();
        $user = $this->getUser();
        $souks = $soukRepository->findByParticipant($user);
        $form = $this->createForm(ProductFormType::class, $product, [
            'souks' => $souks
        ]);

        $form->handleRequest($request);
        $product->setOwner($this->getUser());
        if ($form->isSubmitted() && $form->isValid()) {
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $destination = $photoDir . DIRECTORY_SEPARATOR . $fileName;
                //$photo->move($photoDir, $fileName);
                copy($photo->getPathname(), $destination);
            }
            $product->setImage($fileName);
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('app_dashboard_client');
        }
        return $this->render('souk/produits/create.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/product/{id}', name: 'single_product', requirements: ['id' => '\d+'])]
    public function singleProducts($id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        return $this->render('souk/singleProducts.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'update_products')]
    public function updateProducts($id, Request $request, Product $product, SoukRepository $soukRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $souks = $soukRepository->findByParticipant($user);
        $form = $this->createForm(ProductFormType::class, $product, [
            'souks' => $souks
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_dashboard_client', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('souk/produits/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'product_delet')]
    public function deleteOffer($id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        $em->remove($product);
        $em->flush();
        $this->addFlash('success', 'Offer deleted successfully.');
        return $this->redirectToRoute('app_dashboard_client');
    }
}
