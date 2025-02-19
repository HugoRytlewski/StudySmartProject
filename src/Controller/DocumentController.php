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

final class DocumentController extends AbstractController{
    #[Route('/document', name: 'app_document')]
    public function index(): Response
    {
        return $this->render('document/index.html.twig', [
            'controller_name' => 'DocumentController',
        ]);
    }

    #[Route('/document', name: 'app_document')]
    public function upload(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
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
                    $pdfFile->move(
                        $this->getParameter('pdf_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                    return $this->redirectToRoute('document_upload');
                }

                $document->setNomDuFichier($originalFilename);
                $document->setChemin($newFilename);
                $document->setUploadAt(new \DateTimeImmutable());
                
                $em->persist($document);
                $em->flush();

                $this->addFlash('success', 'Fichier PDF importé avec succès.');

                /* return $this->redirectToRoute('document_list'); */
            }
        }

        return $this->render('document/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
