<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;


final class FrontController extends AbstractController{
    #[Route('/', name: 'app_loading')]
    public function index(): Response
    {
        return $this->render('front/loading.html.twig', [
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
public function home(): Response 
{
    $user = $this->getUser();
    return $this->render('front/index.html.twig', [
        'nom' => $user->getFirstName(),
        'phrase_motivation' => 'Phrase de motivation !',
        'evenement' => [
            'matiere' => 'Anglais',
            'description' => 'Faire la vidéo',
            'date' => '05/04/2025'
        ],
        'calendrier' => [
            'nom' => 'CV UE310',
            'debut' => '18h00',
            'fin' => '19h00'
        ],
        'message' => [
            'auteur' => 'Hillel',
            'texte' => "T'a fait l'anglais ??"
        ]
    ]);
}
}
