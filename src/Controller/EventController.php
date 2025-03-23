<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


final class EventController extends AbstractController
{
    private $security;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, Security $security)
    {
        $this->security = $security;
        $this->httpClient = $httpClient;
    }
    #[Route('/event', name: 'app_event')]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/api/events', name: 'api_events', methods: ['GET'])]
    public function getEvents(EventRepository $eventRepository): JsonResponse
    {
        $user = $this->security->getUser();
        $events = $eventRepository->findBy(['user' => $user]);

        $formattedEvents = array_map(fn($event) => [
            'id' => $event->getId(),
            'title' => $event->getTitre(),
            'start' => $event->getDateStart()->format('Y-m-d\TH:i:s'),
            'end' => $event->getDateEnd()->format('Y-m-d\TH:i:s'),
        ], $events);

        return $this->json($formattedEvents);
    }

    #[Route('/api/add-event', name: 'api_add_event', methods: ['POST'])]
    public function addEvent(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->security->getUser();

            if (!$data || !isset($data['titre']) || !isset($data['start']) || !isset($data['end'])) {
                throw new \Exception('Invalid data');
            }

            // Remove timezone information if present
            $data['start'] = preg_replace('/(\+|-)\d{2}:\d{2}/', '', $data['start']);
            $data['end'] = preg_replace('/(\+|-)\d{2}:\d{2}/', '', $data['end']);


            $event = new Event();
            $event->setTitre($data['titre']);
            $event->setDateStart(new \DateTime($data['start']));
            $event->setDateEnd(new \DateTime($data['end']));
            $event->setUser($user);

            $em->persist($event);
            $em->flush();

            return new JsonResponse(['id' => $event->getId(), 'status' => 'Event created!'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/add-ics-events', name: 'api_add_ics_events', methods: ['POST'])]
    public function addIcsEvents(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $events = $data['events'];
        $user = $this->security->getUser();

        foreach ($events as $eventData) {
            $event = new Event();
            $event->setTitre($eventData['title']);
            $event->setDateStart(new \DateTime($eventData['start']));
            $event->setDateEnd(new \DateTime($eventData['end']));
            $event->setUser($user);
            $em->persist($event);
        }

        $em->flush();

        return new JsonResponse(['status' => 'ICS Events created!'], 201);
    }

    #[Route('/api/delete-event/{id}', name: 'api_delete_event', methods: ['DELETE'])]
    public function deleteEvent(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        $em->remove($event);
        $em->flush();

        return new JsonResponse(['status' => 'Event deleted!'], 200);
    }

}
