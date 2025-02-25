<?php

namespace App\Controller;

use App\Entity\Livraison;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AbstractController
{
    #[Route('/payment/process/{id}', name: 'payment_process', methods: ['POST'])]
    public function processPayment(Livraison $livraison, Request $request, EntityManagerInterface $em): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $user = $this->getUser();

        // Vérifier si l'utilisateur est bien l'expéditeur ou le destinataire
        if ($user !== $livraison->getIdExpediteur() && $user !== $livraison->getIdDistinataire()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'effectuer ce paiement.");
        }

        try {
            $token = $request->request->get('stripeToken');
            $amount = (float) $request->request->get('montant') * 100; // Convertir en centimes
            $email = $request->request->get('email');
            if ($amount <= 0) {
                throw new \Exception("Montant invalide.");
            }

            $charge = Charge::create([
                'amount' => $amount,
                'currency' => 'eur',
                'description' => 'Paiement sur le site',
                'source' => $token,
                'receipt_email' => $email,
            ]);

            // Mettre à jour l'état du paiement
            if ($user === $livraison->getIdExpediteur()) {
                $livraison->setPaymentExp('Payé');
            } elseif ($user === $livraison->getIdDistinataire()) {
                $livraison->setPaymentDist('Payé');
            }
            
            $em->flush();

            $this->addFlash('success', 'Paiement effectué avec succès !');
            return $this->redirectToRoute('livraison_list');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('payment_page', ['id' => $livraison->getId()]);
        }
    }


    #[Route('/payment/{id}', name: 'payment_page')]
    public function paymentPage(Livraison $livraison): Response
    {
        return $this->render('payment/payment2.html.twig', [
            'livraison' => $livraison, // ✅ Ajout de la variable
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
        ]);
    }
   
    
}
