<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Document;
use App\Entity\Annotation;

final class AnnotationController extends AbstractController
{
    #[Route('/annotation', name: 'app_annotation')]
    public function index(): Response
    {
        return $this->render('annotation/index.html.twig', [
            'controller_name' => 'AnnotationController',
        ]);
    }

    #[Route('/annotation/save', name: 'save_annotation', methods: ['POST'])]
    public function saveAnnotation(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['documentId'], $data['contenu'], $data['positionX'], $data['positionY'])) {
            return new JsonResponse(['error' => 'Données manquantes.'], 400);
        }

        // Vérification que le document existe
        $document = $entityManager->getRepository(Document::class)->find($data['documentId']);
        if (!$document) {
            return new JsonResponse(['error' => 'Document introuvable.'], 404);
        }

        // Vérification de l'utilisateur
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], 401);
        }

        // Vérifier que l'utilisateur appartient au document
        if ($document->getUser() !== $user) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        // Vérification du contenu de l'annotation
        if (empty($data['contenu'])) {
            return new JsonResponse(['error' => 'Le contenu de l\'annotation ne peut pas être vide.'], 400);
        }

        $annotation = new Annotation();
        $annotation->setContenu($data['contenu']);
        $annotation->setPositionX($data['positionX']);
        $annotation->setPositionY($data['positionY']);
        $annotation->setDocument($document);
        $annotation->setUser($user);

        try {
            $entityManager->persist($annotation);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement de l\'annotation.'], 500);
        }

        return new JsonResponse([
            'success' => true,
            'annotation' => [
                'id' => $annotation->getId(),
                'contenu' => $annotation->getContenu(),
                'positionX' => $annotation->getPositionX(),
                'positionY' => $annotation->getPositionY(),
            ]
        ]);
    }
}
