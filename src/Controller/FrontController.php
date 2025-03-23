<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EventRepository;
use App\Repository\MessageRepository;


final class FrontController extends AbstractController{
    #[Route('/', name: 'app_loading')]
    public function index(): Response
    {
        return $this->render('front/loading.html.twig', [
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
public function home(EventRepository $eventRepository, MessageRepository $MessageRepository): Response
{
    $user = $this->getUser();
    $lastEvent = $eventRepository->getLast4Events($user->getId());
    $lastMessages = $MessageRepository->getLastMessagesByPerson($this->getUser());
    return $this->render('front/index.html.twig', [
        'nom' => $user->getFirstName(),
        'phrase_motivation' => 'Phrase de motivation !',
        'lastEvent' => $lastEvent,
        'calendrier' => [
            'nom' => 'CV UE310',
            'debut' => '18h00',
            'fin' => '19h00'
        ],
        'lastMessages' => $lastMessages
    ]);
}
}
