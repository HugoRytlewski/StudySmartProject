<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(): Response
    {
        return $this->render('calendar/index.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }

    #[Route('/api/add-event', name: 'api_add_event', methods: ['POST'])]
    public function addEvent(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['titre']) || !isset($data['start']) || !isset($data['end'])) {
                throw new \Exception('Invalid data');
            }

            $event = new Event();
            $event->setTitre($data['titre']);
            $event->setDateStart(new \DateTime($data['start']));
            $event->setDateEnd(new \DateTime($data['end']));

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

        foreach ($events as $eventData) {
            $event = new Event();
            $event->setTitre($eventData['title']);
            $event->setDateStart(new \DateTime($eventData['start']));
            $event->setDateEnd(new \DateTime($eventData['end']));
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
