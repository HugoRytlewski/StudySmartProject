<?php 

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'messages_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $lastMessages = $entityManager->getRepository(Message::class)->getLastMessagesByPerson($this->getUser());
         $user = $this->getUser();
         $allUsers = $entityManager->getRepository(Message::class)->findByUsersWithoutConversation($user);
         

         if($lastMessages)
            {
                $id = $lastMessages[0]->getSender() === $user ? $lastMessages[0]->getRecipient()->getId() : $lastMessages[0]->getSender()->getId();
            }else{
                $id = 0;
            }

        return $this->render('message/index.html.twig', [
            'nom' => $user->getFirstName(),
            'messages' => null,
            'lastMessages' => $lastMessages,
            'allUsers' => $allUsers,
            'idPeople' => 0,
            'lastId' => $id
        ]);
    }

    #[Route('/messages/new/{id}', name: 'messages_new')]
    public function new(EntityManagerInterface $entityManager, Request $request, $id,UserRepository $userRepository): Response
    {

        $user = $this->getUser();
        $message = new Message();

        $MessageSend= $request->request->get('message');
        //dd($MessageSend);
        $recipient = $request->request->get('selectedOption');
        if ($recipient === null) {
            $recipient = $userRepository->find($id);
            
        }else{
            $recipient = $userRepository->find($recipient);

        }

        $message->setContent($MessageSend);
        $message->setSender($user);
        $message->setRecipient($recipient);
        $message->setCreatedAt(new \DateTime());
        $entityManager->persist($message);
        $entityManager->flush();


        return $this->redirectToRoute('messages_show', ['id' => $message->getId()]);
    }
    
#[Route('/messages/check', name: 'messages_check')]
public function checkMessages(messageRepository $messageRepository): JsonResponse
{
    $user = $this->getUser();
    $isNew = $messageRepository->findUnreadMessages($user);
    return $this->json($isNew);

    
}


    #[Route('/messages/{id}', name: 'messages_show')]
    public function show(EntityManagerInterface $entityManager, Message $message): Response
    {
        $user = $this->getUser();
        
        if ($message->getSender() !== $user && $message->getRecipient() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette conversation');
        }
    
        $lastMessages = $entityManager->getRepository(Message::class)->getLastMessagesByPerson($user);
        $messages = $entityManager->getRepository(Message::class)->getConversationFromMessage($message);
    
        foreach ($messages as $msg) {
            if ($msg->getRecipient() === $user && !$msg->isRead()) {
                $msg->markAsRead();
            }
        }
        $entityManager->flush();

        $id = $message->getSender() === $user ? $message->getRecipient()->getId() : $message->getSender()->getId();
        $allUsers = $entityManager->getRepository(Message::class)->findByUsersWithoutConversation($user);

    
        return $this->render('message/index.html.twig', [
            'nom' => $user->getFirstName(),
            'messages' => $messages,
            'lastMessages' => $lastMessages, 
            'idPeople' => $id  ,
            'allUsers' => $allUsers,
            'lastId' => $lastMessages[0]->getId()

            ]);
    }

    #[Route('/messages/latest/{partnerId}/{lastId}', name: 'messages_latest')]
    public function latestMessages(MessageRepository $messageRepository, int $partnerId, int $lastId): JsonResponse
    {
        $currentUserId = $this->getUser()->getId();
        $newMessages = $messageRepository->findNewMessages($lastId, $currentUserId, $partnerId); 
        return $this->json($newMessages);
    }
    




}
    

