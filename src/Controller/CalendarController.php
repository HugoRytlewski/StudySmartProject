<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\OpenRouterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;




final class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(): Response
    {
        return $this->render('calendar/index.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }

    #[Route('/calendar/ia/{id}', name: 'app_calendar_ia', methods: ['GET'])]
    public function ia(OpenRouterService $openAi,int $id,EntityManagerInterface $em): Response
    {
        $evenementUser = $this->getUser()->getEvents();
        $event = $em->getRepository(Event::class)->find($id);

        $evenementArray = [];
    
        foreach ($evenementUser as $evenement) {
            $datestart = $evenement->getDatestart()->format('Y-m-d H:i:s');
            $dateend = $evenement->getDateend()->format('Y-m-d H:i:s');
            
            $user = ['id' => $evenement->getUser()->getId(),];
    
            $evenementArray[] = [
                'id' => $evenement->getId(),
                'titre' => $evenement->getTitre(),
                'datestart' => $datestart,
                'dateend' => $dateend,
                'user' => $user, 
            ];
        }
    
        $evenementJson = json_encode($evenementArray);
        $eventSpecific = json_encode($event->getTitre());

        $result = $openAi->getCalendar($evenementJson, $eventSpecific);
        $content = $result['choices'][0]['message']['content'];
        $contentJson = json_decode($content, true);
        $newEvent = new Event();




        $newEvent->setTitre($contentJson['title']);
        $newEvent->setDatestart(new \DateTime($contentJson['datestart']));
        $newEvent->setDateend(new \DateTime($contentJson['dateend']));
        $user = $em->getRepository(User::class)->find($contentJson['user'][0]['id']);
        $newEvent->setUser($user);
        $em->persist($newEvent);
        $em->flush();
        return $this->redirectToRoute('app_calendar');

   
}
}
