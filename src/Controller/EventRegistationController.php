<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\ParticipantEvent;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class EventRegistationController extends AbstractController
{

    #[Route('/event/{id}/register', name: 'app_event_registration')]
    public function event_register(Request $request, Event $event, EntityManagerInterface $entityManager, $id)
    {
        if ($event->isFullyBooked()) {
            $this->addFlash('error', "cette évenement a atteint le nombre maximal des participant!");
            return $this->redirectToRoute('app_event_index');
        }




        $participant = new ParticipantEvent();
        $participant->setUser($this->getUser());
        $participant->setEvent($event);
        $participant->setInscriptionDate(new \DateTime());
        $entityManager->persist($participant);
        $event->decrementParticipant();

        $entityManager->persist($event);

        $entityManager->flush();

        $this->addFlash('success', 'vous avez enregistré dans cette évenement avec success!');
        return $this->redirectToRoute('app_event_index');
    }

    #[Route("planning/{id}", name: "user_planning")]
    function planning(Event $event, SessionRepository $sessionRepository)
    {
        $user = $this->getUser();
        $sessions = $event->getSessions();
        return $this->render('Dashboard/events/planning.html.twig', [
            'sessions' => $sessions,
            'user' => $user,
        ]);
    }
}
