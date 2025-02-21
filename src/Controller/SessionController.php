<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\AttendanceRepository;
use App\Repository\ParticipantEventRepository;
use App\Repository\SessionRepository;
use App\Service\JwtService;
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
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/session')]
final class SessionController extends AbstractController{

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

#[Route('/session/{id}/join', name: 'join_online_session')]
public function joinSession(Session $session, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    $meetingRoom = 'session_' . $session->getId();
    $isOrganizer = $session->getEvent()->getOrgniser() === $user;

    // If organizer, set meeting as started
    if ($isOrganizer) {
        $session->setMeetingStarted(true);
        $entityManager->persist($session);
        $entityManager->flush();
    }

    // Allow both organizer and participants to join
    return $this->render('session/meet.html.twig', [
        'meetingRoom' => $meetingRoom,
        'session' => $session,
        'isOrganizer' => $isOrganizer,
       
    ]);
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
        'user' => $user,
        'event' => $session->getEvent()
    ]);

    if (!$participantEvent && $session->getEvent()->getOrgniser() != $user) {
        throw $this->createNotFoundException('Vous n\'êtes pas inscrit à cet événement.');
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

    // Send the attendance code via email
    // $organizerEmail = $session->getEvent()->getOrgniser()->getEmail();
    // $participantEmail = $participantEvent->getUser()->getEmail();
    $now = new \DateTime();
    $endHour = $session->getEndHour();
    $remainingMinutes = $endHour > $now ? $now->diff($endHour)->i : 0;
    // $email = (new Email())
    //     ->from($organizerEmail)
    //     ->to($participantEmail)
    //     ->subject('Votre code de présence')
    //     ->html($this->renderView('mail/index.html.twig', [
    //         'participant_name' => $participantEvent->getUser()->getEmail(),
    //         'session_name' => $session->getObjective(),
    //         'attendance_code' => $attendance->getCode(),
    //         'organizer_name' => $session->getEvent()->getOrgniser()->getEmail(),
    //         'event_name' => $session->getEvent()->getTitle(),
    //     ]));

    
    //         $mailer->send($email);
 
   

    // If organizer, set meeting as started
    $isOrganizer = $session->getEvent()->getOrgniser() === $user;
    if ($isOrganizer) {
        $session->setMeetingStarted(true);
        $entityManager->persist($session);
        $entityManager->flush();
    }

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





    
}
