<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function getLastMessages($user)
    {
        return $this->createQueryBuilder('m')
            ->where('m.recipient = :user OR m.sender = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function getLastMessagesByPerson($user)
{
    $qb = $this->createQueryBuilder('m1');
    
    return $qb->where(
            $qb->expr()->orX(
                $qb->expr()->eq('m1.sender', ':user'),
                $qb->expr()->eq('m1.recipient', ':user')
            )
        )
        ->andWhere(
            'NOT EXISTS (
                SELECT m2.id 
                FROM App\Entity\Message m2 
                WHERE (
                    (m2.sender = m1.sender AND m2.recipient = m1.recipient) OR 
                    (m2.sender = m1.recipient AND m2.recipient = m1.sender)
                )
                AND m2.createdAt > m1.createdAt
            )'
        )
        ->setParameter('user', $user)
        ->orderBy('m1.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

public function getMessageHistory($currentUser, $idMessage)
{
    return $this->createQueryBuilder('m')
        ->where('m.sender = :currentUser OR m.recipient = :currentUser')
        ->andWhere('m.id = :idMessage')
        ->setParameter('currentUser', $currentUser)
        ->setParameter('idMessage', $idMessage)
        ->getQuery()
        ->getOneOrNullResult();
}

public function getConversationFromMessage(Message $message)
{
    $sender = $message->getSender();
    $recipient = $message->getRecipient();

    return $this->createQueryBuilder('m')
        ->where('(m.sender = :sender AND m.recipient = :recipient) OR (m.sender = :recipient AND m.recipient = :sender)')
        ->setParameter('sender', $sender)
        ->setParameter('recipient', $recipient)
        ->orderBy('m.createdAt', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findByUsersWithoutConversation(User $user): array
{
    $entityManager = $this->getEntityManager();
    
    $dql = "
        SELECT u
        FROM App\Entity\User u
        WHERE u.id != :userId
        AND u.id NOT IN (
            SELECT DISTINCT IDENTITY(m1.sender)
            FROM App\Entity\Message m1
            WHERE m1.recipient = :user
        )
        AND u.id NOT IN (
            SELECT DISTINCT IDENTITY(m2.recipient)
            FROM App\Entity\Message m2
            WHERE m2.sender = :user
        )
    ";

    $query = $entityManager->createQuery($dql);
    $query->setParameters([
        'userId' => $user->getId(),
        'user' => $user
    ]);
    
    return $query->getResult();
}

public function findNewMessages(int $lastId, int $senderId, int $recipientId): array
{
    return $this->createQueryBuilder('m')
        ->select('m.id, m.content, sender.id as sender_id, sender.firstName')
        ->join('m.sender', 'sender')
        ->where('m.id > :lastId')
        ->andWhere('(m.sender = :senderId AND m.recipient = :recipientId) OR (m.sender = :recipientId AND m.recipient = :senderId)')
        ->setParameter('lastId', $lastId)
        ->setParameter('senderId', $senderId)
        ->setParameter('recipientId', $recipientId)
        ->orderBy('m.id', 'ASC')
        ->getQuery()
        ->getArrayResult();
}

public function findUnreadMessages(User $user): bool
{
    $count = $this->createQueryBuilder('m')
    ->select('COUNT(m.id)')
    ->where('m.recipient = :user')
    ->andWhere('m.isRead = false')
    ->setParameter('user', $user)
    ->getQuery()
    ->getSingleScalarResult();

return $count > 0;
}


}
    