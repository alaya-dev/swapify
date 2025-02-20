<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Certificate;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CertificateGeneratorService
{
    private $twig;
    private $projectDir;
    private $entityManager;
    private $urlGenerator;
    private $filesystem;
    private $qrCodeBuilder;
   
    public function __construct(
        Environment $twig, 
        
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag
    ) {
        $this->twig = $twig;
        $this->projectDir = $parameterBag->get('kernel.project_dir');
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->filesystem = $filesystem;
    }

    public function generateCertificate(User $user, Event $event,#[Autowire('%projectDir%')] string $projectDir, array $participationStats): Certificate
    {
        // Create a new certificate entity
        $certificate = new Certificate();
        $certificate->setUser($user);
        $certificate->setEvent($event);
        $certificate->setDateAcquisition(new \DateTime());
        $certificate->setValid(true);
        
        // Persist to get an ID first
        $this->entityManager->persist($certificate);
        $this->entityManager->flush();
        
        
        
        // Generate PDF and save it
        $pdfPath = $this->generatePdf($user, $event, $participationStats, $certificate,$projectDir);
        
        // Save changes
        $this->entityManager->flush();
        
        return $certificate;
    }

    
    private function generatePdf(User $user, Event $event, array $participationStats, Certificate $certificate, #[Autowire('%projectDir%')] string $projectDir): string
    {
        // Create directory for certificates if it doesn't exist
        $certificateDir = $projectDir;

        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        
        // Render the certificate template
        $html = $this->twig->render('dashboard/certificates/pdf_template.html.twig', [
            'user' => $user,
            'event' => $event,
            'participationStats' => $participationStats,
            'certificate' => $certificate,
            'generatedDate' => $certificate->getDateAcquisition(),
        ]);
        
        // Load HTML into Dompdf
        $dompdf->loadHtml($html);
        
        // Render the PDF
        $dompdf->render();
        
        // Save PDF - specify the correct path relative to project root
        $pdfFileName = $certificate->getId() . '.pdf';
        $certifsDir = $projectDir;
       
        $pdfFullPath = $certifsDir .'/'. $pdfFileName;
        file_put_contents($pdfFullPath, $dompdf->output());
        
        // Return path relative to web root (public directory)
        return $pdfFullPath;
    }
    
    public function generateCertificateResponse(Certificate $certificate): Response
{
    // Validate that certificate exists and is valid
    if (!$certificate->isValid()) {
        throw new \Exception('Certificate is not valid');
    }
    
    // Get PDF path
    $pdfPath = $this->projectDir  .'/public/uploads/certifs/'. $certificate->getId() . '.pdf';
    
    // Check if file exists
    if (!file_exists($pdfPath)) {
        throw new \Exception('Certificate file not found');
    }
    
    // Return PDF response
    return new Response(
        file_get_contents($pdfPath),
        Response::HTTP_OK,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificate_' . $certificate->getId() . '.pdf"'
        ]
    );
}
}