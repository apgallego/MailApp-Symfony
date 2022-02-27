<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutboxController extends AbstractController
{
    #[Route('/outbox', name: 'outbox')]
    public function index(MessageRepository $messageRepository, UserRepository $userRepository): Response
    {

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        // $messages = $messageRepository->findBy(['senderID' => $userID]);
        $messages = $messageRepository->createQueryBuilder('m')
            ->andWhere('m.senderID = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('m.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
        $users = $userRepository->findAll();

        // $messages = $messageRepository->findBy(['message_sent' => $user->getMessageSent()]);
        
        return $this->render('outbox/outbox.html.twig', [
            'controller_name' => 'OutboxController',
            'messages' => $messages,
            'users' => $users
        ]);
    }
}
