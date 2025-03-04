<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Image;
use App\Enum\etat;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ImageRepository;
use App\Repository\ParticipantEventRepository;
use App\Service\QrCodeService;
use App\Service\VideoSDKService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/event')]
final class EventController extends AbstractController{
    #[Route('/all',name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        
        
        return $this->render('events/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }





    
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request,  EntityManagerInterface $entityManager,#[Autowire('%uploads_directory%')] string $photoDir): Response
    {
        $event = new Event();
        $event->setOrgniser($this->getUser());
        $event->setStatus((etat::EnAttente)->label());
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $destination = $photoDir . DIRECTORY_SEPARATOR . $fileName;
                copy($photo->getPathname(), $destination);
            }
            $event->setImage($fileName);
    
            foreach ($event->getSessions() as $session) {
                $session->setEvent($event);
                $entityManager->persist($session);
                $session->setMeetingStarted(false);
            }
    
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Événement ajouté avec succès.');
            return $this->redirectToRoute('my_events');
        }
        $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
    
        return $this->render('dashboard/events/ajouter_event.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(EventRepository $eventRepository,Event $event): Response
    {
       
       
        return $this->render('dashboard/events/event_detail.html.twig', [
           
            'event' =>$event,
        ]);
    }
    #[Route('details/{id}', name: 'app_event', methods: ['GET'])]
    public function eventById(EventRepository $eventRepository,Event $event): Response
    {
       
       
        return $this->render('events/event.html.twig', [
           
            'event' =>$event,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, #[Autowire('%uploads_directory%')] string $photoDir): Response
    {
        $originalImage = $event->getImage();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $destination = $photoDir . DIRECTORY_SEPARATOR . $fileName;
                copy($photo->getPathname(), $destination);
                $event->setImage($fileName);
            } else {
                $event->setImage($originalImage);
            }
            $entityManager->flush();
    
            $this->addFlash('success', 'Event updated successfully.');
    
            return $this->redirectToRoute('my_events', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('dashboard/events/edit_event.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}/delete', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {

        if (!$event->getCertificates()->isEmpty()) {
            $this->addFlash('error', 'Cet événement ne peut pas être supprimé car des certificats y sont attachés.');
            return $this->redirectToRoute('my_events');
        }
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
    
            $this->addFlash('success', 'Event deleted successfully.');
        }
    
        return $this->redirectToRoute('my_events', [], Response::HTTP_SEE_OTHER);
    }

   
    #[Route('/',name:'my_events')]
    public function myEvents(EventRepository $eventRepository,Request $request,ParticipantEventRepository $participantEventRepository){
            $filter = $request->query->get('filter', 'all');
            $allevents = $eventRepository->findAll(); 
            $events = match ($filter) {
                'pending' => $eventRepository->findBy(['status' => 'En Attente', 'orgniser' => $this->getUser()]),
                'active' => $eventRepository->findBy(['status' => 'Acceptée', 'orgniser' => $this->getUser()]),
                'inactive' => $eventRepository->findBy(['status' => 'Rejetée', 'orgniser' => $this->getUser()]),
                default => $eventRepository->findBy(['orgniser' => $this->getUser()]),
            };    
        return $this->render('dashboard/events/mesEvents.html.twig', [
           'events' => $events,
           'allEvents'=>$allevents,
           'participantsEvent'=>$participantEventRepository->findBy(["user"=>$this->getUser()])
        ]);
    

    }

}
