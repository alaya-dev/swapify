<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\ParticipantEvent;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\AttendanceRepository;
use App\Repository\ParticipantEventRepository;
use App\Repository\SessionRepository;
use App\Service\JwtService;
use App\Service\MailerMailJetService;
use App\Service\VideoSDKService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/session')]
final class SessionController extends AbstractController{


    private MailerMailJetService $mailerService;

    public function __construct(
        MailerMailJetService $mailerService,
      
    ) {
        $this->mailerService=$mailerService;
      
    }

    #[Route(name: 'app_session_index', methods: ['GET'])]
    public function index(SessionRepository $sessionRepository): Response
    {
        return $this->render('session/index.html.twig', [
            'sessions' => $sessionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_session_delete', methods: ['POST'])]
    public function delete(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$session->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
    }


#[Route('/session/{id}/check-status', name: 'check_meeting_status')]
public function checkStatus(Session $session): JsonResponse
{
    // No authentication check needed for status
    return $this->json([
        'meetingStarted' => $session->isMeetingStarted()
    ]);
}

private function scheduleAttendanceEmail(Session $session,Attendance $attendance,): void
    {
        $delay = $session->getEndHour()->getTimestamp() - (new \DateTime('+15 minutes'))->getTimestamp();
        $code =$attendance->getCode();
    
        if ($delay > 0 && $attendance->getParticipantEvent()!=null ) {
            $user=$attendance->getParticipantEvent()->getUser();
            $htmlContent='
                <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Code de Présence</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
            .email-header { background-color:rgb(0, 103, 2); color: #ffffff; text-align: center; padding: 20px; }
            .email-header h1 { margin: 0; font-size: 24px; }
            .email-body { padding: 20px; color: #333333; }
            .email-body h2 { color:rgb(0, 103, 2); font-size: 28px; text-align: center; margin: 20px 0; }
            .email-body p { font-size: 16px; line-height: 1.6; }
            .email-footer { text-align: center; padding: 20px; background-color: #f4f4f4; font-size: 14px; color: #666666; }
            .logo { display: block; margin: 0 auto 20px; width: 100px; }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Code de Présence pour la Session</h1>
            </div>
            <div class="email-body">
                <img src="/public/logo.png" alt="Logo" class="logo">
                <p>Bonjour'  . $user->getNom().' '.$user->getPreNom()  .' ,</p>
                <p>Votre code de présence pour la session <strong> '. $session->getEvent()->getTitle() .' </strong> est :</p>
                <h2>' . $code .' </h2>
                <p>Veuillez entrer ce code dans l\'interface de la session pour confirmer votre présence.</p>
                <p>Merci et à bientôt !</p>
            </div>
            <div class="email-footer">
                <p>Si vous n\'avez pas demandé ce code, veuillez ignorer cet e-mail.</p>
                <p>&copy; 2024 Swapify. Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>';
            $this->mailerService->sendEmail($user->getEmail(),"Code d'attendance pour la session intitulé {{$session->getEvent()->getTitle()}}",$htmlContent,);
           
        }
    }


    private function sendLocationEmail(Session $session,ParticipantEventRepository $participantEventRepo): void
    {
        
        $participantEvents = $participantEventRepo->findBy([
            'event' => $session->getEvent()
        ]);

        if (empty($participantEvents)) {
            $this->addFlash('error', 'No participants found for this session.');
            
        }
        else {
             foreach ($participantEvents as $participantEvent) {

            $htmlContent='
                <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Code de Présence</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
            .email-header { background-color:rgb(0, 103, 2); color: #ffffff; text-align: center; padding: 20px; }
            .email-header h1 { margin: 0; font-size: 24px; }
            .email-body { padding: 20px; color: #333333; }
            .email-body h2 { color:rgb(0, 103, 2); font-size: 28px; text-align: center; margin: 20px 0; }
            .email-body p { font-size: 16px; line-height: 1.6; }
            .email-footer { text-align: center; padding: 20px; background-color: #f4f4f4; font-size: 14px; color: #666666; }
            .logo { display: block; margin: 0 auto 20px; width: 100px; }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Code de Présence pour la Session</h1>
            </div>
            <div class="email-body">
                <img src="/public/logo.png" alt="Logo" class="logo">
                <p>Bonjour'  . $participantEvent->getUser()->getNom().' '.$participantEvent->getUser()->getPreNom()  .' ,</p>
                <p>Voici la localisation pour la session presentiel intitulé <strong> '. $session->getEvent()->getTitle() .' </strong> est :</p>
                <p><a href="https://maps.app.goo.gl/6h5EYdcTNJsVCnZg9" target="_blank" style="color:rgb(0, 103, 2); font-weight:bold;">Voir sur Google Maps</a></p>
                <p>Merci et à bientôt !</p>
            </div>
            <div class="email-footer">
                <p>Si vous n\'avez pas demandé ce code, veuillez ignorer cet e-mail.</p>
                <p>&copy; 2024 Swapify. Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>';
            $this->mailerService->sendEmail($participantEvent->getUser()->getEmail(),"Localisation pour la session intitulé {{$session->getEvent()->getTitle()}}",$htmlContent,);
        }

        }

       
        
    }
#[Route('/session/{id}/join-session', name: 'join_online_sessionn')]
public function joinSessions(
    Session $session,
    ParticipantEventRepository $participantEventRepo,
    EntityManagerInterface $entityManager,
    AttendanceRepository $attendanceRepository,
    MailerInterface $mailer
): Response {
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Find the participant event record for this user and event
    $participantEvent = $participantEventRepo->findOneBy([
        'event' => $session->getEvent()
    ]);
    

    if (!$participantEvent && $session->getEvent()->getOrgniser() != $user) {
        $this->addFlash('error','l\evenement doit avoir au moin un particpant ');
    }

    // Find existing attendance record
    $attendance = $attendanceRepository->findOneBy([
        'participantEvent' => $participantEvent,
        'session' => $session
    ]);

    // If attendance does not exist, create it
    if (!$attendance) {
        $attendance = new Attendance();
        $attendance->setAttended(false);
        $attendance->setParticipantEvent($participantEvent);
        $attendance->setSession($session);
        $attendance->setTimestamp(new DateTime());
        $attendance->setCode(random_int(100000, 999999));

        $entityManager->persist($attendance);
        $entityManager->flush();
    }

    

    $now = new \DateTime();
    $endHour = $session->getEndHour();
    $remainingMinutes = $endHour > $now ? $now->diff($endHour)->i : 0;
    
    $isOrganizer = $session->getEvent()->getOrgniser() === $user;
    if ($isOrganizer) {
        $session->setMeetingStarted(true);
        $entityManager->persist($session);
        $entityManager->flush();
    }
    $this->scheduleAttendanceEmail($session,$attendance);
    // Pass the VideoSDK API key
    $apiKey = $this->getParameter('videosdk_api_key');

    // Allow both organizer and participants to join
    return $this->render('Dashboard/events/meet.html.twig', [
        'meetingRoom' => 'session_' . $session->getId(),
        'session' => $session,
        'isOrganizer' => $isOrganizer,
        'apiKey' => $apiKey,
        "remainingMinutes"=>$remainingMinutes,
        "attendance_id"=>$attendance->getId()
        
    ]);
}



#[Route('/session/{id}/location', name: 'sendInpPrsonSessionLocation')]
public function sendLocation(
    Session $session,
    ParticipantEventRepository $participantEventRepo,
    EntityManagerInterface $entityManager,
    AttendanceRepository $attendanceRepository,
): Response {
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $participantEvents = $participantEventRepo->findBy([
        'event' => $session->getEvent()
    ]);

    // Loop through each participant and create an attendance record if it doesn't exist
    foreach ($participantEvents as $participantEvent) {
        $attendance = $attendanceRepository->findOneBy([
            'participantEvent' => $participantEvent,
            'session' => $session
        ]);

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->setAttended(false); // Set attended status to false
            $attendance->setParticipantEvent($participantEvent);
            $attendance->setSession($session);
            $attendance->setTimestamp(new DateTime());
            $entityManager->persist($attendance);
            $entityManager->flush();
        }



    }
    $this->sendLocationEmail($session,$participantEventRepo);
    $this->addFlash('Success',"localisaion send successfully!");

    return $this->redirectToRoute('app_event_show',['id' => $session->getEvent()->getId()]);
}


#[Route('/session/{id}/attendance', name: 'view_attendance')]
public function viewAttendance(
    Session $session,
    ParticipantEventRepository $participantEventRepo,
    AttendanceRepository $attendanceRepository
): Response {
    // Fetch all participant events for this session
    $participantEvents = $participantEventRepo->findBy([
        'event' => $session->getEvent()
    ]);

    // Fetch attendance records for each participant
    $attendanceRecords = [];
    foreach ($participantEvents as $participantEvent) {
        $attendance = $attendanceRepository->findOneBy([
            'participantEvent' => $participantEvent,
            'session' => $session
        ]);
        $attendanceRecords[$participantEvent->getId()] = $attendance ? $attendance->isAttended() : false;
    }

    return $this->render('Dashboard/events/attendance_list.html.twig', [
        'session' => $session,
        'participantEvents' => $participantEvents,
        'attendanceRecords' => $attendanceRecords, // Pass attendance records to the template
    ]);
}


#[Route('/session/{id}/attendance/update', name: 'update_attendance', methods: ['POST'])]
public function updateAttendance(
    Request $request,
    Session $session,
    ParticipantEventRepository $participantEventRepo,
    AttendanceRepository $attendanceRepository,
    EntityManagerInterface $entityManager
): Response {
    // Get the submitted attendance data
    $attendanceData = $request->request->all('attendance');
    
    // Fetch all participant events for this session
    $participantEvents = $participantEventRepo->findBy([
        'event' => $session->getEvent()
    ]);

    // Loop through each participant and update their attendance status
    foreach ($participantEvents as $participantEvent) {
        $attendance = $attendanceRepository->findOneBy([
            'participantEvent' => $participantEvent,
            'session' => $session
        ]);

        $attendance->setAttended((bool)($attendanceData[$participantEvent->getId()] ?? false));
        $entityManager->persist($attendance);
    }

    // Save all changes to the database
    $entityManager->flush();

    // Redirect back to the attendance list with a success message
    $this->addFlash('success', 'Attendance updated successfully.');
    return $this->redirectToRoute('view_attendance', ['id' => $session->getId()]);
}




    
}
