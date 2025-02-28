<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use App\Service\mailerMailJetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/contrat')]
final class ContratController extends AbstractController
{
    #[Route(name: 'app_contrat_index', methods: ['GET'])]
    public function index(ContratRepository $contratRepository): Response
    {
        return $this->render('contrat/index.html.twig', [
            'contrats' => $contratRepository->findAll(),
        ]);
    }
    #[Route('/mesContrats/signed', name: 'app_mesContratsSigned', methods: ['GET'])]
    public function mesContratsSigned(ContratRepository $contratRepository): Response
    {
        $user = $this->getUser();
    
        $contrats = $contratRepository->createQueryBuilder('c')
            ->leftJoin('c.offre', 'o')
            ->where('(o.offerMaker = :user AND c.signeeOffreMaker = true)')
            ->orWhere('(o.annonceOwner = :user AND c.signeeOwnerAnnonce = true)')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    
        foreach ($contrats as $contrat) {
            $contrat->decodedClauses = json_decode($contrat->getClauses(), true);
        }
    
        return $this->render('contrat/mesContrats.html.twig', [
            'contrats' => $contrats,
        ]);
    }
    
    
    #[Route('/mesContrats/aSignee', name: 'app_mesContratsAsignee', methods: ['GET'])]
    public function mesContratsASignee(ContratRepository $contratRepository): Response
    {
        $user = $this->getUser();
    
        $contrats = $contratRepository->createQueryBuilder('c')
            ->leftJoin('c.offre', 'o')
            ->where('(o.offerMaker = :user AND c.signeeOffreMaker = false)')
            ->orWhere('(o.annonceOwner = :user AND c.signeeOwnerAnnonce = false)')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    
        foreach ($contrats as $contrat) {
            $contrat->decodedClauses = json_decode($contrat->getClauses(), true);
        }
    
        return $this->render('contrat/mesContrats.html.twig', [
            'contrats' => $contrats,
        ]);
    }
    

      



    #[Route('/mesContrats/pret', name: 'app_mesContratsPret', methods: ['GET'])]
    public function mesContratsPret(ContratRepository $contratRepository): Response
    {
        $user = $this->getUser();
    
        $contrats = $contratRepository->createQueryBuilder('c')
            ->leftJoin('c.offre', 'o')
            ->Where('o.offerMaker = :user')
            ->orWhere('o.annonceOwner = :user')
            ->andWhere('c.signeeOffreMaker = true')
            ->andWhere('c.signeeOwnerAnnonce = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

            foreach ($contrats as $contrat) {
                $contrat->decodedClauses = json_decode($contrat->getClauses(), true);
            }
     
        return $this->render('contrat/mesContrats.html.twig', [
            'contrats' => $contrats,
        ]);
    }
    




    #[Route('/new', name: 'app_contrat_new', methods: ['GET', 'POST'])]
    public function new( mailerMailJetService $mailer ,Request $request, EntityManagerInterface $entityManager, ContratRepository $contratRepository): Response
    {
        $contrat = new Contrat();
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $offre = $contrat->getOffre();
    
            $existingContrat = $contratRepository->findOneBy(['offre' => $offre]);
    
            if ($existingContrat) {
                $this->addFlash('error', 'Un contrat existe déjà pour cette offre.');
                return $this->redirectToRoute('app_contrat_new');
            }
    
            $clauses = [
                "Objet du Contrat" => "Les parties conviennent d’un échange d’objet entre " . $offre->getAnnonceOwner()->getNom() . " " . $offre->getAnnonceOwner()->getPrenom() . " et " . $offre->getOfferMaker()->getNom() . " " . $offre->getOfferMaker()->getPrenom() . ".",
                "État des Objets" => "Les objets doivent être conformes à leur description.",
                "Obligations des Parties" => "Chaque partie garantit l’authenticité et la conformité de l’objet.",
                "Annulation" => "Toute annulation doit être signalée avant l’échange.",
                "Responsabilité" => "La plateforme décline toute responsabilité en cas de litige.",
                "Droit Applicable" => "Ce contrat est régi par la loi de Tunis."
            ];
    
            $contrat->setClauses(json_encode($clauses));


            if ($request->request->has('signer')) {
                $contrat->setSigneeOwnerAnnonce(true);
            }
    
            $entityManager->persist($contrat);
            $entityManager->flush();


 // Construct email content MailJet
$subject = 'Signature de votre contrat';

$content = sprintf(
    "Bonjour %s,\n\n".
    "Nous vous informons qu'un contrat a été ajouté en lien avec votre offre. " .
    "Nous vous invitons à vous rendre dans votre espace personnel sur notre plateforme pour consulter et signer ce contrat.\n\n" .
    "Nous restons à votre disposition pour toute question.\n\n" .
    "Cordialement,\n" .
    "L’équipe de la swapify",
    $offre->getOfferMaker()->getEmail(),
    'https://127.0.0.1:8000/' 
);

if ($mailer instanceof \App\Service\mailerMailJetService) {
    $mailer->sendEmail($offre->getOfferMaker()->getEmail(), $subject, $content);
} else {
    throw new \LogicException('Expected mailerMailJetService as MailerInterface implementation.');
}


    
    
            $this->addFlash('success', 'Le contrat est crée avec succée .');
            return $this->redirectToRoute('app_mesContratsSigned', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('contrat/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }
    
    

    #[Route('/{id}', name: 'app_contrat_show', methods: ['GET'])]
    public function show(Contrat $contrat): Response
    {
        return $this->render('contrat/show.html.twig', [
            'contrat' => $contrat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contrat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_delete', methods: ['POST'])]
    public function delete(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contrat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contrat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/contrat/signeeAnnonceCrea/{id}', name: 'app_signeMonContrat', methods: ['POST'])]
    public function app_signeMonContrat(Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user !== $contrat->getOffre()->getAnnonceOwner()) {
            $this->addFlash('error', 'Vous ne pouvez pas signer ce contrat.');
            return $this->redirectToRoute('app_mesContratsPret');
        }
    
        // Changer le statut de signature
        $contrat->setSigneeOwnerAnnonce(true);
        $entityManager->persist($contrat);
        $entityManager->flush();
    
        $this->addFlash('success', 'Contrat signé avec succès.');
        return $this->redirectToRoute('app_mesContratsPret');
    }


    #[Route('/contrat/signeeOM/{id}', name: 'app_signeMonContratOM', methods: ['POST'])]
    public function app_signeMonContratOM(Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user !== $contrat->getOffre()->getOfferMaker()) {
            $this->addFlash('error', 'Vous ne pouvez pas signer ce contrat.');
            return $this->redirectToRoute('app_mesContratsPret');
        }
    
        // Changer le statut de signature
        $contrat->setSigneeOffreMaker(true);
        $entityManager->persist($contrat);
        $entityManager->flush();
    
        $this->addFlash('success', 'Contrat signé avec succès.');
        return $this->redirectToRoute('app_mesContratsPret');
    }
 




/**
 * @Route("/contrat/{id}/download-pdf", name="app_download_pdfContrat")
 */
public function downloadPdf(Contrat $contrat): Response
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    $html = $this->renderView('contrat/pdf_template.html.twig', [
        'contrat' => $contrat,
    ]);

    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'portrait');

    $dompdf->render();

    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="contrat_' . $contrat->getId() . '.pdf"'
        ]
    );
}

}
