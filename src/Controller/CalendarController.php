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
        $data = json_decode($request->getContent(), true);
        dump($data);
        $event = new Event();
        $event->setTitre($data['titre']);
        $event->setDateStart(new \DateTime($data['start']));
        $event->setDateEnd(new \DateTime($data['end']));

        $em->persist($event);
        $em->flush();

        return new JsonResponse(['status' => 'Event created!'], 201);
    }

}
