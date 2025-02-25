<?php

namespace App\Controller;

use App\Entity\Certificate;
use App\Entity\Event;
use App\Form\CertificateType;
use App\Repository\CertificateRepository;
use App\Repository\EventRepository;
use App\Service\CertificateGeneratorService;
use App\Service\ParticipationCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\EventRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class CertificateController extends AbstractController{
    
    #[Route('/all',name: 'app_certificate_index', methods: ['GET'])]
    public function index(CertificateRepository $certificateRepository): Response
    {
        return $this->render('certificate/index.html.twig', [
            'certificates' => $certificateRepository->findAll(),
        ]);
    }

    #[Route('/event/{id}/generate-certificate', name: 'app_certificate_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        Event $event,
        ParticipationCalculatorService $participationCalculator,
        CertificateRepository $certificateRepository,
        CertificateGeneratorService $certificateGenerator,
        #[Autowire('%projectDir%')] string $projectDir
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        // Check if the user already has a valid certificate for this event
        $existingCertificate = $certificateRepository->findOneBy([
            'user' => $user,
            'event' => $event,
            'valid' => true
        ]);
        
        if ($existingCertificate) {
            // If certificate exists, return it
            return $certificateGenerator->generateCertificateResponse($existingCertificate);
        }
        
        // Calculate participation stats
        $participationStats = $participationCalculator->calculateParticipationPercentage($user, $event);
        
        // Check eligibility
        if (!$participationStats['isCertificateEligible']) {
            $this->addFlash('error', 'You are not eligible for a certificate for this event.');
            return $this->redirectToRoute('check_certificate_eligibility', ['id' => $event->getId()]);
        }
        
        // Generate the certificate
        $certificate = $certificateGenerator->generateCertificate($user, $event, $projectDir, $participationStats);
        
        // Return PDF response
        return $certificateGenerator->generateCertificateResponse($certificate);
    }

    

    #[Route('/certificate/{id}', name: 'app_certificate_show', methods: ['GET'])]
    public function show(Certificate $certificate,
    CertificateGeneratorService $certificateGenerator): Response
    {$user = $this->getUser();
        
        // Check if the user is the owner of the certificate or an admin
        if ($user !== $certificate->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You cannot view this certificate.');
        }
        
        // Generate and return PDF response
        return $certificateGenerator->generateCertificateResponse($certificate);
   
    }

    #[Route('verify-certif/{id}', name:"verify_certif")]
    public function verifyCertificate(Certificate $certificate): Response
    {
        return $this->render('certificate/verify.html.twig', [
            'certificate' => $certificate,
            'isValid' => $certificate->isValid()
        ]);
    }



    
    #[Route('/stats', name: "check_certificate_eligibility")]
    public function checkCertificateEligibility(
        EventRepository $eventRepository,
        ParticipationCalculatorService $participationCalculator,
        CertificateRepository $certificateRepository,
        Request $request
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $events = $eventRepository->findValidateEvents();
        $participationStats = []; // Initialize an array
        
        foreach ($events as $event) {
            $existingCertificate = $certificateRepository->findOneBy([
                'user' => $user,
                'event' => $event,
                'valid' => true
            ]);
            
            $participationStats[] = [
                'event' => $event,
                'stats' => $participationCalculator->calculateParticipationPercentage($user, $event),
                'isCertificateGenerated' => $existingCertificate !== null,
            ];
        }
        
        $filter = $request->query->get('filter', 'all');
        // Apply the filter
        $filteredStats = $this->filterParticipationStats($participationStats, $filter);
        
        return $this->render('dashboard/events/stats.html.twig', [
            'participationStats' => $filteredStats,
            'filter' => $filter // Pass the filter to the template
        ]);
    }

    private function filterParticipationStats(array $participationStats, string $filter): array
{
    if ($filter === 'all') {
        return $participationStats;
    }
    
    return array_filter($participationStats, function($stat) use ($filter) {
        if (!isset($stat['stats'])) {
            return false;
        }
        
        // Check if stats is an array or object and access accordingly
        $isEligible = is_array($stat['stats']) 
            ? $stat['stats']['isCertificateEligible'] 
            : $stat['stats']->isCertificateEligible;
        
        if ($filter === 'eligible') {
            return $isEligible === true;
        } elseif ($filter === 'not-eligible') {
            return $isEligible === false;
        }
        
        return true;
    });
}
    



    
}
