<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
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
        $events = $eventRepository->findAll();

        $formattedEvents = array_map(fn($event) => [
            'id' => $event->getId(),
            'titre' => $event->getTitre(),
            'start' => $event->getDatestart()->format('Y-m-d\TH:i:s'),
            'end' => $event->getDateend()->format('Y-m-d\TH:i:s'),
        ], $events);

        return $this->json($formattedEvents);
    }
}
