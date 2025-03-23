<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Entity\DocumentCommentaire;
use App\Form\CommentaireDocumentType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\OpenRouterService;
use League\CommonMark\CommonMarkConverter;
use Smalot\PdfParser\Parser;

final class DocumentController extends AbstractController{
    #[Route('/document', name: 'app_document')]
    public function index(EntityManagerInterface $em, Request $request, SluggerInterface $slugger, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour voir vos documents.");
        }

        $documents = $em->getRepository(Document::class)->findBy(['user' => $user], ['uploadAt' => 'DESC']);

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pdfFile = $form->get('pdfFile')->getData();

            if ($pdfFile) {
                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pdfFile->guessExtension();

                try {
                    $pdfFile->move($this->getParameter('pdf_directory'), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload.');
                }

                $document->setNomDuFichier($originalFilename);
                $document->setChemin($newFilename);
                $document->setUploadAt(new \DateTimeImmutable());
                $document->setUser($user); 

                $em->persist($document);
                $em->flush();

                $this->addFlash('success', 'Fichier ajouté avec succès !');
                return $this->redirectToRoute('app_document');
            }
        }

        return $this->render('document/index.html.twig', [
            'documents' => $documents,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/document/{id}', name: 'document_show')]
public function show(Document $document, Request $request, EntityManagerInterface $em, Security $security): Response
{
    $user = $security->getUser();
        if ($document->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à ce document.");
        }
    // Récupérer les commentaires existants
    $commentaires = $document->getDocumentCommentaires();

    // Créer un nouveau commentaire
    $commentaire = new DocumentCommentaire();
    $form = $this->createForm(CommentaireDocumentType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setDocument($document);
        $commentaire->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($commentaire);
        $em->flush();

        $this->addFlash('success', 'Commentaire ajouté avec succès.');

        return $this->redirectToRoute('document_show', ['id' => $document->getId()]);
    }

    return $this->render('document/document_show.html.twig', [
        'document' => $document,
        'commentaires' => $commentaires,
        'form' => $form->createView(),
    ]);
}

#[Route('/document/{id}/reformule', name: 'document_reformule')]
public function reformule(Document $document, EntityManagerInterface $em, Security $security, OpenRouterService $openAi): Response
{
    $user = $security->getUser();
        if ($document->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à ce document.");
        }   

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile('../public/uploads/pdf/' . $document->getChemin());        
        $text = $pdf->getText();
        $result = $openAi->getResponse($text);
        $content = $result['choices'][0]['message']['content'];

        $converter = new CommonMarkConverter();
        $htmlContent = $converter->convert($content);

        return $this->render('document/reformule.html.twig', [
            'document' => $document,
            'reformulatedContent' => $htmlContent,
        ]);
}
}